<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use BackedEnum;
use Cline\StateMachine\Contracts\BoundStateMachineInterface;
use Cline\StateMachine\Contracts\StateAccessorInterface;
use Cline\StateMachine\Events\StateTransitionAnnounce;
use Cline\StateMachine\Events\StateTransitionCan;
use Cline\StateMachine\Events\StateTransitioned;
use Cline\StateMachine\Events\StateTransitionEntering;
use Cline\StateMachine\Events\StateTransitionFailed;
use Cline\StateMachine\Events\StateTransitioning;
use Cline\StateMachine\Events\StateTransitionLeaving;
use Cline\StateMachine\Exceptions\TransitionBlockedException;
use Cline\StateMachine\Exceptions\TransitionNotAllowedException;
use Cline\StateMachine\Registry\StateGraphRegistry;
use Cline\StateMachine\StateGraph;
use Cline\StateMachine\Transition;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumUnitCase;
use Throwable;
use UnitEnum;

use function array_reverse;
use function in_array;
use function sprintf;
use function throw_if;
use function throw_unless;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.4
 *
 * @psalm-immutable
 */
final readonly class EnumStateMachine implements BoundStateMachineInterface
{
    public function __construct(
        private object $subject,
        private string $attribute,
        private Dispatcher $events,
        private StateGraphRegistry $maps,
        private Application $application,
        private StateAccessorInterface $accessor,
    ) {}

    public function using(?string $attribute = null): self
    {
        if ($attribute === null || $attribute === $this->attribute) {
            return $this;
        }

        return new self(
            subject: $this->subject,
            attribute: $attribute,
            events: $this->events,
            maps: $this->maps,
            application: $this->application,
            accessor: $this->accessor,
        );
    }

    public function enumClass(): string
    {
        $value = $this->current();

        return $value::class;
    }

    public function current(): object
    {
        return $this->accessor->get($this->subject, $this->attribute);
    }

    // Direct, by-target transitions are intentionally not part of the public API.

    /**
     * Named guard check.
     *
     * @param array<string, mixed> $context
     */
    public function can(BackedEnum $name, array $context = []): bool
    {
        $from = $this->current();
        $map = $this->maps->resolve($from::class);
        $named = $map->getNamed($name->value);

        if ($named === null) {
            return false;
        }

        if (!in_array($from->name, $named['from'], true)) {
            return false;
        }

        $to = $this->enumCaseByName($from::class, (string) $named['to']);
        $can = new StateTransitionCan($this->subject, $this->attribute, $from, $to, $name->value, $named['properties'], $context);
        $this->events->dispatch($can);

        return !$can->isBlocked();
    }

    /**
     * Apply a named transition.
     *
     * @param array<string, mixed> $context
     */
    public function apply(BackedEnum $name, array $context = [], bool $persist = true): void
    {
        $from = $this->current();
        $enumClass = $from::class;
        $map = $this->maps->resolve($enumClass);
        $named = $map->getNamed($name->value);

        throw_if($named === null, TransitionNotAllowedException::between(enumClass: $enumClass, from: $from->name, to: $name->value));

        throw_unless(in_array($from->name, $named['from'], true), TransitionNotAllowedException::between(enumClass: $enumClass, from: $from->name, to: $named['to']));

        $to = $this->enumCaseByName($enumClass, (string) $named['to']);

        // Guard stage (named)
        $can = new StateTransitionCan($this->subject, $this->attribute, $from, $to, $name->value, $named['properties'], $context);
        $this->events->dispatch($can);

        throw_if($can->isBlocked(), TransitionBlockedException::named(enumClass: $enumClass, name: $name->value, reason: $can->reason()));

        $transition = new Transition(subject: $this->subject, attribute: $this->attribute, from: $from, to: $to, context: $context);
        // Leaving and Transitioning stages
        $this->events->dispatch(
            new StateTransitionLeaving($this->subject, $this->attribute, $from, $to, $name->value, $named['properties'], $context),
        );
        $this->events->dispatch(
            new StateTransitioning(subject: $this->subject, attribute: $this->attribute, from: $from, to: $to),
        );

        try {
            $this->runPipeline($transition, $map, function (Transition $t) use ($persist): void {
                $this->applyCore($t, $persist);
            });
        } catch (Throwable $throwable) {
            $this->events->dispatch(
                new StateTransitionFailed(
                    subject: $this->subject,
                    attribute: $this->attribute,
                    from: $from,
                    to: $to,
                    exception: $throwable,
                ),
            );

            throw $throwable;
        }

        $this->events->dispatch(
            new StateTransitioned(
                subject: $this->subject,
                attribute: $this->attribute,
                from: $from,
                to: $to,
            ),
        );
        // Announce available transitions from the new state
        $available = $this->reachableTransitions($context);
        $this->events->dispatch(
            new StateTransitionAnnounce(
                subject: $this->subject,
                attribute: $this->attribute,
                current: $to,
                availableNames: $available,
                context: $context,
            ),
        );
    }

    /**
     * @return list<string> Transition names (values) reachable from current state after guard stage
     */
    public function reachableTransitions(array $context = []): array
    {
        $from = $this->current();
        $enumClass = $from::class;
        $map = $this->maps->resolve($enumClass);
        $names = [];

        foreach ($map->transitionNames() as $name) {
            $spec = $map->getNamed($name);

            if ($spec === null) {
                continue;
            }

            if (!in_array($from->name, $spec['from'], true)) {
                continue;
            }

            $to = $this->enumCaseByName($enumClass, (string) $spec['to']);
            $can = new StateTransitionCan($this->subject, $this->attribute, $from, $to, $name, $spec['properties'], $context);
            $this->events->dispatch($can);

            if ($can->isBlocked()) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    private function enumCaseByName(string $enumClass, string $name): UnitEnum
    {
        $ref = new ReflectionEnum($enumClass);
        $case = $ref->getCase($name);

        throw_if(!$case instanceof ReflectionEnumUnitCase, InvalidArgumentException::class, sprintf('No enum case %s for %s', $name, $enumClass));

        return $case->getValue();
    }

    private function applyCore(Transition $transition, bool $persist): void
    {
        // Entering stage just before applying state
        $this->events->dispatch(
            new StateTransitionEntering(subject: $transition->subject, attribute: $transition->attribute, from: $transition->from, to: $transition->to),
        );
        $this->accessor->set($this->subject, $this->attribute, $transition->to);

        if (!$persist) {
            return;
        }

        $this->accessor->persist($this->subject);
    }

    private function runPipeline(Transition $transition, StateGraph $map, callable $destination): void
    {
        $pipeline = array_reverse($map->middlewares);
        $next = $destination;

        foreach ($pipeline as $middlewareClass) {
            $middleware = $this->application->make($middlewareClass);
            $prevNext = $next;
            $next = function (Transition $t) use ($middleware, $prevNext): void {
                $middleware->handle($t, $prevNext);
            };
        }

        $next($transition);
    }
}
