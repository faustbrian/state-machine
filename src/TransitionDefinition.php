<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use BackedEnum;
use Illuminate\Support\Arr;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.2
 *
 * @psalm-immutable
 */
final readonly class TransitionDefinition
{
    /**
     * @param BackedEnum           $name       Typed transition name enum
     * @param list<BackedEnum>     $fromCases  Enum case instances (must match one enum class)
     * @param BackedEnum           $toCase     Enum case instance
     * @param array<string, mixed> $properties Domain metadata
     */
    public function __construct(
        public BackedEnum $name,
        public array $fromCases,
        public BackedEnum $toCase,
        public array $properties = [],
    ) {}

    /**
     * @param BackedEnum|list<BackedEnum> $from       Enum case or list of cases
     * @param array<string, mixed>        $properties
     */
    public static function define(BackedEnum $name, BackedEnum|array $from, BackedEnum $to, array $properties = []): self
    {
        return new self(name: $name, fromCases: Arr::wrap($from), toCase: $to, properties: $properties);
    }
}
