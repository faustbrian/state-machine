<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Contracts;

/**
 * Abstraction for reading/writing the state attribute on a subject.
 *
 * Implementations may adapt Eloquent models, DTOs, aggregates, etc.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface StateAccessorInterface
{
    /**
     * Read the enum state value from the given attribute.
     */
    public function get(object $subject, string $attribute): object;

    /**
     * Write the enum state value to the given attribute.
     */
    public function set(object $subject, string $attribute, object $state): void;

    /**
     * Persist the subject if supported; otherwise a no-op.
     */
    public function persist(object $subject): void;
}
