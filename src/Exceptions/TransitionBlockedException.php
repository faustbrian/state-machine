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
final class TransitionBlockedException extends RuntimeException
{
    public static function named(string $enumClass, string $name, ?string $reason = null): self
    {
        $msg = "Transition '{$name}' was blocked for {$enumClass}";

        if ($reason) {
            $msg .= ": {$reason}";
        }

        return new self($msg);
    }
}
