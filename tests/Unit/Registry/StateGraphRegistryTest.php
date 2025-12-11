<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Registry\StateGraphRegistry;
use Cline\StateMachine\StateGraph;
use Cline\StateMachine\Tests\Fixtures\OrderStatus;

test('can resolve state graph for enum', function (): void {
    $registry = new StateGraphRegistry();

    // This would typically load from compiled cache or discovery
    $stateGraph = $registry->resolve(OrderStatus::class);

    expect($stateGraph)->toBeInstanceOf(StateGraph::class);
});

test('caches resolved state graphs', function (): void {
    $registry = new StateGraphRegistry();

    $first = $registry->resolve(OrderStatus::class);
    $second = $registry->resolve(OrderStatus::class);

    // Should be the same instance due to caching
    expect($first)->toBe($second);
});

test('can compile all discovered state graphs', function (): void {
    $registry = new StateGraphRegistry();

    $compiled = $registry->compileAll();

    expect($compiled)->toBeArray();
});

test('can clear compiled cache', function (): void {
    $registry = new StateGraphRegistry();

    // This should not throw an exception
    $registry->clear();

    expect(true)->toBeTrue(); // Just verify it completes
});
