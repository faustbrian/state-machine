<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\StateGraphBuilder;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;
use Cline\StateMachine\Tests\Fixtures\TransitionName;

test('can create state graph builder with enum class', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    expect($builder->enumClass())->toBe(OrderStatus::class);
});

test('can allow single transition between states', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowTransition(OrderStatus::Pending, OrderStatus::Processing);

    $graph = $builder->build();

    expect($graph->allows('Pending', 'Processing'))->toBeTrue();
    expect($graph->allows('Pending', 'Shipped'))->toBeFalse();
});

test('can allow multiple transitions from single state', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowTransition([OrderStatus::Pending, OrderStatus::Processing], OrderStatus::Cancelled);

    $graph = $builder->build();

    expect($graph->allows('Pending', 'Cancelled'))->toBeTrue();
    expect($graph->allows('Processing', 'Cancelled'))->toBeTrue();
});

test('can create named transitions', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowNamed(TransitionName::Fulfill, OrderStatus::Processing, OrderStatus::Shipped);

    $graph = $builder->build();

    expect($graph->getNamed('fulfill'))->not->toBeNull();
    expect($graph->transitionNames())->toContain('fulfill');
});

test('can allow multiple from states for named transition', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowNamed(TransitionName::Cancel, [OrderStatus::Pending, OrderStatus::Processing], OrderStatus::Cancelled);

    $graph = $builder->build();

    $namedData = $graph->getNamed('cancel');
    expect($namedData)->not->toBeNull();
    expect($namedData['from'])->toContain('Pending');
    expect($namedData['from'])->toContain('Processing');
});

test('can set transition properties', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowNamed(TransitionName::Ship, OrderStatus::Processing, OrderStatus::Shipped, [
        'requires_payment' => true,
        'notify_customer' => true,
    ]);

    $graph = $builder->build();
    $namedData = $graph->getNamed('ship');

    expect($namedData['properties'])->toBe([
        'requires_payment' => true,
        'notify_customer' => true,
    ]);
});

test('can ignore same state transitions', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->ignoreSameState(true);
    $builder->allowTransition(OrderStatus::Pending, OrderStatus::Pending);

    $graph = $builder->build();

    // Should still record the rule but with ignoreSameState flag
    expect($graph->ignoreSameState)->toBeTrue();
});

test('can build empty state graph', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $graph = $builder->build();

    expect($graph->enumClass)->toBe(OrderStatus::class);
    expect($graph->rules)->toBeEmpty();
    expect($graph->named)->toBeEmpty();
});

test('can allow all transitions', function (): void {
    $builder = new StateGraphBuilder(OrderStatus::class);

    $builder->allowAllTransitions();

    $graph = $builder->build();

    // Should have created all possible transitions
    expect($graph->allows('Pending', 'Processing'))->toBeTrue();
    expect($graph->allows('Pending', 'Shipped'))->toBeTrue();
    expect($graph->allows('Processing', 'Delivered'))->toBeTrue();
});
