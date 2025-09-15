<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use Cline\StateMachine\Accessor\ObjectStateAccessor;
use Cline\StateMachine\Contracts\BoundStateMachineInterface;
use Cline\StateMachine\Contracts\StateAccessorInterface;
use Cline\StateMachine\Contracts\StateMachineInterface;
use Cline\StateMachine\Registry\StateGraphRegistry;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class StateMachine implements StateMachineInterface
{
    public function __construct(
        private Dispatcher $events,
        private StateGraphRegistry $registry,
        private Application $app,
        private StateAccessorInterface $accessor = new ObjectStateAccessor(),
    ) {}

    public function for(object $subject): BoundStateMachineInterface
    {
        return new EnumStateMachine(
            subject: $subject,
            attribute: 'state',
            events: $this->events,
            maps: $this->registry,
            application: $this->app,
            accessor: $this->accessor,
        );
    }
}
