<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Contracts;

use Cline\StateMachine\StateGraphBuilder;

/**
 * Marker + contract for state graph classes.
 *
 * Implementations must provide a static configure() method
 * that populates a StateGraphBuilder for a particular enum.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.0
 */
interface StateGraphInterface
{
    public static function configure(StateGraphBuilder $map): void;
}
