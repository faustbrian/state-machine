<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Accessor\ObjectStateAccessor;
use Cline\StateMachine\Tests\Fixtures\Order;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('can get state from object property', function (): void {
    $order = new Order(['status' => OrderStatus::Pending]);
    $accessor = new ObjectStateAccessor();

    $state = $accessor->get($order, 'status');

    expect($state)->toBe(OrderStatus::Pending);
});

test('can set state on object property', function (): void {
    $order = new Order(['status' => OrderStatus::Pending]);
    $accessor = new ObjectStateAccessor();

    $accessor->set($order, 'status', OrderStatus::Processing);

    expect($order->status)->toBe(OrderStatus::Processing);
});

test('can get state from object with getter method', function (): void {
    $object = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;

        public function getStatus(): OrderStatus
        {
            return $this->status;
        }
    };

    $accessor = new ObjectStateAccessor();

    $state = $accessor->get($object, 'status');

    expect($state)->toBe(OrderStatus::Pending);
});

test('can set state on object with setter method', function (): void {
    $object = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;

        public function setStatus(OrderStatus $status): void
        {
            $this->status = $status;
        }

        public function getStatus(): OrderStatus
        {
            return $this->status;
        }
    };

    $accessor = new ObjectStateAccessor();

    $accessor->set($object, 'status', OrderStatus::Processing);

    expect($object->getStatus())->toBe(OrderStatus::Processing);
});

test('throws exception for non-existent property', function (): void {
    $object = new stdClass();
    $accessor = new ObjectStateAccessor();

    expect(fn () => $accessor->get($object, 'nonexistent'))
        ->toThrow(InvalidArgumentException::class);
});

test('can persist non-model objects', function (): void {
    $object = new stdClass();
    $accessor = new ObjectStateAccessor();

    // Should be no-op for non-Model objects
    $accessor->persist($object);

    expect(true)->toBeTrue();
});
