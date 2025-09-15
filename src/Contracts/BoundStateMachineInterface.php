<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Contracts;

use BackedEnum;

/**
 * A state machine bound to a specific subject and attribute.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface BoundStateMachineInterface
{
    /**
     * Override the attribute name used for state; defaults to 'state'.
     */
    public function using(?string $attribute = null): self;

    /**
     * Return the current enum state value.
     */
    public function current(): object;

    /**
     * Return the enum class of the current state.
     */
    public function enumClass(): string;

    /**
     * Determine if the named transition is allowed given optional context.
     *
     * @param array<string, mixed> $context
     */
    public function can(BackedEnum $name, array $context = []): bool;

    /**
     * Apply the named transition.
     *
     * @param array<string, mixed> $context
     */
    public function apply(BackedEnum $name, array $context = [], bool $persist = true): void;

    /**
     * @return list<string> Transition names reachable after guard stage
     */
    public function reachableTransitions(array $context = []): array;
}
