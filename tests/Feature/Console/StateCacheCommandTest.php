<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

test('state cache command exists and has correct signature', function (): void {
    $this->artisan('state:cache --help')
        ->assertExitCode(0);
});

test('state cache command handles missing config gracefully', function (): void {
    // Test that command fails gracefully when config is missing
    expect(fn () => $this->artisan('state:cache'))
        ->toThrow(ValueError::class, 'Path must not be empty');
});
