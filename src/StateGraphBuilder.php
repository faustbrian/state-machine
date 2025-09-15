<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use BackedEnum;
use Cline\StateMachine\TransitionDefinition;
use InvalidArgumentException;

use function array_unique;
use function array_values;
use function is_a;
use function is_array;
use function throw_unless;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.3
 */
final class StateGraphBuilder
{
    /** @var array<string, array<string, null>> */
    private array $rules = [];

    private bool $ignoreSameState = false;

    /** @var list<class-string> */
    private array $middlewares = [];

    /** @var array<string, array{from: list<string>, to: string, properties: array<string, mixed>}> name => data */
    private array $named = [];

    public function __construct(
        /** @var class-string */
        private readonly string $enumClass,
    ) {}

    public function enumClass(): string
    {
        return $this->enumClass;
    }

    /**
     * @param BackedEnum|list<BackedEnum> $from Enum case(s)
     * @param BackedEnum                  $to   Enum case
     */
    public function allowTransition(BackedEnum|array $from, BackedEnum $to): self
    {
        $fromCases = is_array($from) ? $from : [$from];

        foreach ($fromCases as $fromCase) {
            $this->assertEnumCase($fromCase);
            $this->assertEnumCase($to);
            $fromName = $fromCase->name;
            $toName = $to->name;
            $this->rules[$fromName][$toName] = null;
        }

        return $this;
    }

    /**
     * Convenience for allowing multiple transitions.
     * Each tuple: [from|from[] case(s), to case]
     *
     * @param list<mixed>|list{0:BackedEnum|list<BackedEnum>, 1:BackedEnum} $tuples
     */
    public function allowTransitions(array $tuples): self
    {
        foreach ($tuples as $tuple) {
            [$from, $to] = $tuple + [null, null];
            $this->allowTransition($from, $to);
        }

        return $this;
    }

    public function allowAllTransitions(): self
    {
        $cases = $this->enumClass::cases();

        foreach ($cases as $from) {
            foreach ($cases as $to) {
                if ($from->name === $to->name) {
                    // keep decision for same-state governed by ignoreSameState
                    $this->rules[$from->name][$to->name] = null;

                    continue;
                }

                $this->rules[$from->name][$to->name] = null;
            }
        }

        return $this;
    }

    public function ignoreSameState(bool $ignore = true): self
    {
        $this->ignoreSameState = $ignore;

        return $this;
    }

    /**
     * @param list<class-string> $middlewareClasses
     */
    public function withMiddleware(array $middlewareClasses): self
    {
        $this->middlewares = array_values($middlewareClasses);

        return $this;
    }

    public function build(): StateGraph
    {
        return new StateGraph($this->enumClass, $this->rules, $this->ignoreSameState, $this->middlewares, $this->named);
    }

    /**
     * Define a named transition.
     *
     * @param list<object>|object  $from       Enum case or list of cases
     * @param object               $to         Enum case
     * @param array<string, mixed> $properties
     */
    public function allowNamed(BackedEnum $name, BackedEnum|array $from, BackedEnum $to, array $properties = []): self
    {
        $fromCases = is_array($from) ? $from : [$from];
        $fromNames = [];

        foreach ($fromCases as $fromCase) {
            $this->assertEnumCase($fromCase);
            $fromNames[] = $fromCase->name;
        }

        $this->assertEnumCase($to);
        $toName = $to->name;

        $this->named[$name->value] = [
            'from' => array_values(array_unique($fromNames)),
            'to' => $toName,
            'properties' => $properties,
        ];

        // Also populate direct rules so transitionTo still works
        foreach ($fromNames as $fromName) {
            $this->rules[$fromName][$toName] ??= null;
        }

        return $this;
    }

    public function allowDefinition(TransitionDefinition $def): self
    {
        return $this->allowNamed($def->name, $def->fromCases, $def->toCase, $def->properties);
    }

    /**
     * @param list<TransitionDefinition> $definitions
     */
    public function allowDefinitions(array $definitions): self
    {
        foreach ($definitions as $def) {
            $this->allowDefinition($def);
        }

        return $this;
    }

    private function assertEnumCase(object $case): void
    {
        throw_unless($case instanceof BackedEnum, new InvalidArgumentException('Expected enum case instance'));

        throw_unless(is_a($case, $this->enumClass), new InvalidArgumentException("Enum case does not belong to {$this->enumClass}"));
    }
}
