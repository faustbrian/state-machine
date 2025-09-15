<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Accessor\ObjectStateAccessor;
use Cline\StateMachine\EnumStateMachine;
use Cline\StateMachine\Registry\StateGraphRegistry;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('can get current state', function (): void {
    $subject = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;
    };

    $stateMachine = new EnumStateMachine(
        subject: $subject,
        attribute: 'status',
        events: app('events'),
        maps: new StateGraphRegistry(),
        application: app(),
        accessor: new ObjectStateAccessor(),
    );

    expect($stateMachine->current())->toBe(OrderStatus::Pending);
});

test('can get enum class', function (): void {
    $subject = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;
    };

    $stateMachine = new EnumStateMachine(
        subject: $subject,
        attribute: 'status',
        events: app('events'),
        maps: new StateGraphRegistry(),
        application: app(),
        accessor: new ObjectStateAccessor(),
    );

    expect($stateMachine->enumClass())->toBe(OrderStatus::class);
});

test('can create new instance with different attribute', function (): void {
    $subject = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;

        public OrderStatus $other_status = OrderStatus::Processing;
    };

    $stateMachine = new EnumStateMachine(
        subject: $subject,
        attribute: 'status',
        events: app('events'),
        maps: new StateGraphRegistry(),
        application: app(),
        accessor: new ObjectStateAccessor(),
    );

    $newMachine = $stateMachine->using('other_status');

    expect($newMachine)->not->toBe($stateMachine);
    expect($newMachine->current())->toBe(OrderStatus::Processing);
});

test('using same attribute returns same instance', function (): void {
    $subject = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;
    };

    $stateMachine = new EnumStateMachine(
        subject: $subject,
        attribute: 'status',
        events: app('events'),
        maps: new StateGraphRegistry(),
        application: app(),
        accessor: new ObjectStateAccessor(),
    );

    $sameMachine = $stateMachine->using('status');

    expect($sameMachine)->toBe($stateMachine);
});

test('using null attribute returns same instance', function (): void {
    $subject = new class()
    {
        public OrderStatus $status = OrderStatus::Pending;
    };

    $stateMachine = new EnumStateMachine(
        subject: $subject,
        attribute: 'status',
        events: app('events'),
        maps: new StateGraphRegistry(),
        application: app(),
        accessor: new ObjectStateAccessor(),
    );

    $sameMachine = $stateMachine->using(null);

    expect($sameMachine)->toBe($stateMachine);
});
