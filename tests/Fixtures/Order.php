<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class Order extends Model
{
    protected $fillable = ['status'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }
}
