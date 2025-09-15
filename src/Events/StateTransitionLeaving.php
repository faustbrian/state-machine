<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Events;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.3
 *
 * @psalm-immutable
 */
final readonly class StateTransitionLeaving
{
    /**
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $context
     */
    public function __construct(
        public object $subject,
        public string $attribute,
        public object $from,
        public object $to,
        public ?string $name = null,
        public array $properties = [],
        public array $context = [],
    ) {}
}
