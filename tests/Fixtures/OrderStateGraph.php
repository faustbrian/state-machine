<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Tests\Fixtures;

use Cline\StateMachine\Contracts\StateGraphInterface;
use Cline\StateMachine\StateGraphBuilder;

/**
 * @psalm-immutable
 */
final readonly class OrderStateGraph implements StateGraphInterface
{
    public static function configure(StateGraphBuilder $builder): void
    {
        $builder
            ->allow(OrderStatus::Pending, [OrderStatus::Processing, OrderStatus::Cancelled])
            ->allow(OrderStatus::Processing, [OrderStatus::Shipped, OrderStatus::Cancelled])
            ->allow(OrderStatus::Shipped, [OrderStatus::Delivered])
            ->named('fulfill', OrderStatus::Processing, OrderStatus::Shipped)
            ->named('cancel', [OrderStatus::Pending, OrderStatus::Processing], OrderStatus::Cancelled);
    }
}
