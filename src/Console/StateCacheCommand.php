<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Console;

use Cline\StateMachine\Registry\StateGraphRegistry;
use Illuminate\Console\Command;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.3
 */
final class StateCacheCommand extends Command
{
    protected $signature = 'state:cache';

    protected $description = 'Compile enum state machine maps to bootstrap/cache';

    public function handle(StateGraphRegistry $registry): int
    {
        $compiled = $registry->compileAll();
        $registry->writeCompiled($compiled);
        $this->components->info('State machine maps cached');

        return self::SUCCESS;
    }
}
