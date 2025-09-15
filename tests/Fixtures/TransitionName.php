<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Tests\Fixtures;

enum TransitionName: string
{
    case Fulfill = 'fulfill';
    case Cancel = 'cancel';
    case Ship = 'ship';
    case Deliver = 'deliver';
}
