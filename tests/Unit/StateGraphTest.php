<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\StateGraph;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('can check if transition is allowed with string values', function (): void {
    $stateGraph = new StateGraph(
        enumClass: OrderStatus::class,
        rules: [
            'pending' => ['processing' => null, 'cancelled' => null],
            'processing' => ['shipped' => null],
        ],
    );

    expect($stateGraph->allows('pending', 'processing'))->toBeTrue();
    expect($stateGraph->allows('pending', 'cancelled'))->toBeTrue();
    expect($stateGraph->allows('pending', 'shipped'))->toBeFalse();
    expect($stateGraph->allows('processing', 'shipped'))->toBeTrue();
});

test('can get transition names', function (): void {
    $stateGraph = new StateGraph(
        enumClass: OrderStatus::class,
        rules: [],
        named: [
            'fulfill' => [
                'from' => ['processing'],
                'to' => 'shipped',
                'properties' => [],
            ],
            'cancel' => [
                'from' => ['pending', 'processing'],
                'to' => 'cancelled',
                'properties' => [],
            ],
        ],
    );

    $transitionNames = $stateGraph->transitionNames();

    expect($transitionNames)->toContain('fulfill');
    expect($transitionNames)->toContain('cancel');
    expect($transitionNames)->toHaveCount(2);
});

test('can get named transition data', function (): void {
    $namedData = [
        'from' => ['processing'],
        'to' => 'shipped',
        'properties' => ['async' => true],
    ];

    $stateGraph = new StateGraph(
        enumClass: OrderStatus::class,
        rules: [],
        named: ['fulfill' => $namedData],
    );

    expect($stateGraph->getNamed('fulfill'))->toBe($namedData);
    expect($stateGraph->getNamed('nonexistent'))->toBeNull();
});

test('allows method returns false for non-existent transitions', function (): void {
    $stateGraph = new StateGraph(
        enumClass: OrderStatus::class,
        rules: ['pending' => []], // Empty rules for pending state
    );

    expect($stateGraph->allows('pending', 'processing'))->toBeFalse();
});

test('state graph is immutable', function (): void {
    $stateGraph = new StateGraph(
        enumClass: OrderStatus::class,
        rules: ['pending' => ['processing' => null]],
    );

    expect($stateGraph->enumClass)->toBe(OrderStatus::class);
    expect($stateGraph->rules)->toBe(['pending' => ['processing' => null]]);
});
