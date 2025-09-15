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
final class StateClearCommand extends Command
{
    protected $signature = 'state:clear';

    protected $description = 'Clear compiled enum state machine maps';

    public function handle(StateGraphRegistry $registry): int
    {
        $registry->clear();
        $this->components->info('State machine maps cleared');

        return self::SUCCESS;
    }
}
