<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Contracts;

/**
 * Factory-style entry point to obtain a state machine bound to a subject.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface StateMachineInterface
{
    /**
     * Bind the machine to a subject; attribute defaults to 'state'.
     */
    public function for(object $subject): BoundStateMachineInterface;
}
