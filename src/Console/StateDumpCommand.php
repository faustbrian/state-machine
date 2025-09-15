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

use const PHP_EOL;

use function array_keys;
use function implode;
use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.3
 */
final class StateDumpCommand extends Command
{
    protected $signature = 'state:dump {enum : Enum FQCN}';

    protected $description = 'Dump a state machine as Mermaid diagram from compiled maps';

    public function handle(StateGraphRegistry $registry): int
    {
        $enum = (string) $this->argument('enum');
        $map = $registry->resolve($enum);

        $lines = [
            '---',
            'title: '.$enum,
            '---',
            'stateDiagram-v2',
        ];

        if ($map->named !== []) {
            foreach ($map->named as $name => $spec) {
                foreach ($spec['from'] as $from) {
                    $lines[] = sprintf('    %s --> %s: %s', $from, $spec['to'], $name);
                }
            }
        } else {
            foreach ($map->rules as $from => $targets) {
                foreach (array_keys($targets) as $to) {
                    if ($from === $to && !$map->ignoreSameState) {
                        continue;
                    }

                    $lines[] = sprintf('    %s --> %s', $from, $to);
                }
            }
        }

        $this->line(implode(PHP_EOL, $lines));

        return self::SUCCESS;
    }
}
