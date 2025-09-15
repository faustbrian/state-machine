<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Exceptions;

use RuntimeException;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.3
 */
final class TransitionNotAllowedException extends RuntimeException
{
    public static function between(string $enumClass, string $from, string $to): self
    {
        return new self("Transition not allowed for {$enumClass} from {$from} to {$to}");
    }
}
