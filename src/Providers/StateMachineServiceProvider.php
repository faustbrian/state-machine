<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Providers;

use Cline\StateMachine\Console\StateCacheCommand;
use Cline\StateMachine\Console\StateClearCommand;
use Cline\StateMachine\Console\StateDumpCommand;
use Cline\StateMachine\Registry\StateGraphRegistry;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.5
 */
final class StateMachineServiceProvider extends ServiceProvider implements DeferrableProvider
{
    #[Override()]
    public function register(): void
    {
        // Singleton binding moved to SharedKernel AppServiceProvider
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                StateCacheCommand::class,
                StateClearCommand::class,
                StateDumpCommand::class,
            ]);
        }
    }

    #[Override()]
    public function provides(): array
    {
        return [StateGraphRegistry::class];
    }
}
