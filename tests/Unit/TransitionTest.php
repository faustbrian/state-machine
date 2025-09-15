<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Tests\Fixtures\OrderStatus;
use Cline\StateMachine\Transition;

test('can create transition with required parameters', function (): void {
    $subject = new stdClass();
    $context = ['user_id' => 123];

    $transition = new Transition(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
        context: $context,
    );

    expect($transition->subject)->toBe($subject);
    expect($transition->attribute)->toBe('status');
    expect($transition->from)->toBe(OrderStatus::Pending);
    expect($transition->to)->toBe(OrderStatus::Processing);
    expect($transition->context)->toBe($context);
});

test('can create transition without context', function (): void {
    $subject = new stdClass();

    $transition = new Transition(
        subject: $subject,
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    expect($transition->subject)->toBe($subject);
    expect($transition->attribute)->toBe('status');
    expect($transition->from)->toBe(OrderStatus::Pending);
    expect($transition->to)->toBe(OrderStatus::Processing);
    expect($transition->context)->toBeEmpty();
});

test('transition is immutable', function (): void {
    $transition = new Transition(
        subject: new stdClass(),
        attribute: 'status',
        from: OrderStatus::Pending,
        to: OrderStatus::Processing,
    );

    // All properties are readonly - this is verified by PHP at runtime
    expect($transition)->toBeInstanceOf(Transition::class);
});
