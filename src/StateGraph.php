<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use function array_key_exists;
use function array_keys;

/**
 * Immutable compiled map of allowed transitions for an enum.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.4
 *
 * @psalm-immutable
 */
final readonly class StateGraph
{
    /**
     * @param array<string, array<string, null>>                                                     $rules
     * @param list<class-string>                                                                     $middlewares
     * @param array<string, array{from: list<string>, to: string, properties: array<string, mixed>}> $named
     */
    public function __construct(
        /** @var class-string */
        public string $enumClass,
        public array $rules,
        public bool $ignoreSameState = false,
        public array $middlewares = [],
        public array $named = [],
    ) {}

    public function allows(string $fromCase, string $toCase): bool
    {
        return array_key_exists($toCase, $this->rules[$fromCase]);
    }

    /**
     * @return list<string>
     */
    public function transitionNames(): array
    {
        return array_keys($this->named);
    }

    public function getNamed(string $name): ?array
    {
        return $this->named[$name] ?? null;
    }
}
