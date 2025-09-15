<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Events\StateTransitionAnnounce;
use Cline\StateMachine\Events\StateTransitionCan;
use Cline\StateMachine\Events\StateTransitioned;
use Cline\StateMachine\Events\StateTransitionEntering;
use Cline\StateMachine\Events\StateTransitionFailed;
use Cline\StateMachine\Events\StateTransitioning;
use Cline\StateMachine\Events\StateTransitionLeaving;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('StateTransitionAnnounce event holds transition data', function (): void {
    $subject = new stdClass();
    $availableNames = ['fulfill', 'cancel'];
    $context = ['user_id' => 123];

    $event = new StateTransitionAnnounce(
        subject: $subject,
        attribute: 'status',
        current: OrderStatus::Pending,
        availableNames: $availableNames,
        context: $context,
    );

    expect($event->subject)->toBe($subject);
    expect($event->attribute)->toBe('status');
    expect($event->current)->toBe(OrderStatus::Pending);
    expect($event->availableNames)->toBe($availableNames);
    expect($event->context)->toBe($context);
});

test('StateTransitionCan event can be blocked', function (): void {
    $event = new StateTransitionCan(
        subject: new stdClass(),
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($event->isBlocked())->toBeFalse();

    $event->block('Insufficient permissions');

    expect($event->isBlocked())->toBeTrue();
    expect($event->reason())->toBe('Insufficient permissions');
});

test('StateTransitionCan event can be blocked without reason', function (): void {
    $event = new StateTransitionCan(
        subject: new stdClass(),
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    $event->block();

    expect($event->isBlocked())->toBeTrue();
    expect($event->reason())->toBeNull();
});

test('StateTransitioned event holds transition data', function (): void {
    $subject = new stdClass();

    $event = new StateTransitioned(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($event->subject)->toBe($subject);
    expect($event->attribute)->toBe('status');
    expect($event->from)->toBe(OrderStatus::Pending);
    expect($event->to)->toBe(OrderStatus::Processing);
});

test('StateTransitionEntering event holds transition data', function (): void {
    $subject = new stdClass();

    $event = new StateTransitionEntering(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($event->subject)->toBe($subject);
    expect($event->to)->toBe(OrderStatus::Processing);
});

test('StateTransitionLeaving event holds transition data', function (): void {
    $subject = new stdClass();

    $event = new StateTransitionLeaving(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($event->subject)->toBe($subject);
    expect($event->from)->toBe(OrderStatus::Pending);
});

test('StateTransitioning event holds transition data', function (): void {
    $subject = new stdClass();

    $event = new StateTransitioning(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($event->subject)->toBe($subject);
    expect($event->attribute)->toBe('status');
    expect($event->from)->toBe(OrderStatus::Pending);
    expect($event->to)->toBe(OrderStatus::Processing);
});

test('StateTransitionFailed event holds error data', function (): void {
    $subject = new stdClass();
    $exception = new Exception('Test error');

    $event = new StateTransitionFailed(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
        exception: $exception,
    );

    expect($event->subject)->toBe($subject);
    expect($event->attribute)->toBe('status');
    expect($event->from)->toBe(OrderStatus::Pending);
    expect($event->to)->toBe(OrderStatus::Processing);
    expect($event->exception)->toBe($exception);
});
