<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Exceptions\TransitionBlockedException;
use Cline\StateMachine\Exceptions\TransitionNotAllowedException;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('TransitionNotAllowedException creates descriptive message', function (): void {
    $exception = TransitionNotAllowedException::between(
        'App\Enums\OrderStatus',
        OrderStatus::Pending->value,
        OrderStatus::Shipped->value,
    );

    expect($exception->getMessage())
        ->toContain('App\Enums\OrderStatus')
        ->toContain('pending')
        ->toContain('shipped')
        ->toContain('Transition not allowed');
});

test('TransitionBlockedException creates descriptive message for named transition', function (): void {
    $exception = TransitionBlockedException::named(
        'App\Enums\OrderStatus',
        'fulfill',
    );

    expect($exception->getMessage())
        ->toContain('App\Enums\OrderStatus')
        ->toContain('fulfill')
        ->toContain('Transition')
        ->toContain('was blocked');
});

test('TransitionBlockedException includes reason when provided', function (): void {
    $exception = TransitionBlockedException::named(
        'App\Enums\OrderStatus',
        'fulfill',
        'Insufficient permissions',
    );

    expect($exception->getMessage())
        ->toContain('Insufficient permissions');
});

test('TransitionBlockedException works without reason', function (): void {
    $exception = TransitionBlockedException::named(
        'App\Enums\OrderStatus',
        'fulfill',
    );

    expect($exception->getMessage())
        ->not->toContain(':')
        ->toContain('was blocked for App\Enums\OrderStatus');
});

test('exceptions extend RuntimeException', function (): void {
    $notAllowed = TransitionNotAllowedException::between('Test', 'a', 'b');
    $blocked = TransitionBlockedException::named('Test', 'transition');

    expect($notAllowed)->toBeInstanceOf(RuntimeException::class);
    expect($blocked)->toBeInstanceOf(RuntimeException::class);
});
