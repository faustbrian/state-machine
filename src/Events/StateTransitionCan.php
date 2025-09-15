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
 * @version 1.0.2
 */
final class StateTransitionCan
{
    private bool $blocked = false;

    private ?string $reason = null;

    /**
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly object $subject,
        public readonly string $attribute,
        public readonly object $from,
        public readonly object $to,
        public readonly ?string $name = null,
        public readonly array $properties = [],
        public readonly array $context = [],
    ) {}

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function block(?string $reason = null): void
    {
        $this->blocked = true;
        $this->reason = $reason;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
