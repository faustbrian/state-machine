<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Contracts;

use Cline\StateMachine\Transition;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.2
 */
interface TransitionMiddlewareInterface
{
    /**
     * @param Transition                 $transition Immutable description of the transition
     * @param callable(Transition): void $next       Proceed to the next middleware/handler
     */
    public function handle(Transition $transition, callable $next): void;
}
