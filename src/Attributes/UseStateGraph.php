<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Attributes;

use Attribute;

/**
 * Attribute to associate an enum with its state graph class.
 *
 * Applied to the enum declaring its single state graph mapping.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.1.1
 *
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseStateGraph
{
    /**
     * @param class-string $graph Fully-qualified graph class name
     */
    public function __construct(
        public string $graph,
    ) {}
}
