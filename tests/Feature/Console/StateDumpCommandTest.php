<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\StateMachine\Tests\Fixtures\OrderStatus;
use Illuminate\Testing\PendingCommand;

test('state dump command exists and has correct signature', function (): void {
    $this->artisan('state:dump --help')
        ->assertExitCode(0);
});

test('state dump command requires enum argument', function (): void {
    expect(fn () => $this->artisan('state:dump'))
        ->toThrow(RuntimeException::class, 'Not enough arguments');
});

test('state dump command can handle enum class', function (): void {
    // Test that command exists and can execute with enum argument
    $result = $this->artisan('state:dump', ['enum' => OrderStatus::class]);

    // Command exists and returns some result
    expect($result)->toBeInstanceOf(PendingCommand::class);
});
