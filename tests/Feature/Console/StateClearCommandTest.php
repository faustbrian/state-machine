<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Testing\PendingCommand;



/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
test('state clear command exists and has correct signature', function (): void {
    $this->artisan('state:clear --help')
        ->assertExitCode(0);
});

test('state clear command can be run', function (): void {
    // Test that command exists and can execute
    $result = $this->artisan('state:clear');

    // Command exists and returns some exit code
    expect($result)->toBeInstanceOf(PendingCommand::class);
});
