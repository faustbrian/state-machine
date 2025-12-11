<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine;

use Cline\StateMachine\Contracts\StateAccessorInterface;
use Cline\StateMachine\Registry\StateGraphRegistry;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use InvalidArgumentException;

use function count;
use function enum_exists;
use function is_string;

/**
 * Factory to create EnumStateMachine instances without coupling to Eloquent models.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.0
 *
 * @psalm-immutable
 */
final readonly class EnumStateMachineFactory
{
    public function __construct(
        private Dispatcher $events,
        private StateGraphRegistry $registry,
        private Application $app,
        private StateAccessorInterface $accessor,
    ) {}

    public function forAttribute(Model $model, string $attribute): EnumStateMachine
    {
        return new EnumStateMachine(
            subject: $model,
            attribute: $attribute,
            events: $this->events,
            maps: $this->registry,
            application: $this->app,
            accessor: $this->accessor,
        );
    }

    public function forEnum(Model $model, string $enumClass): EnumStateMachine
    {
        $attribute = $this->resolveAttributeForEnum($model, $enumClass);

        return $this->forAttribute($model, $attribute);
    }

    public function autoDetect(Model $model): EnumStateMachine
    {
        $attribute = $this->autoDetectEnumAttribute($model);

        return $this->forAttribute($model, $attribute);
    }

    private function autoDetectEnumAttribute(Model $model): string
    {
        $enumAttrs = [];

        foreach ($model->getCasts() as $attr => $cast) {
            if (!is_string($cast) || !enum_exists($cast)) {
                continue;
            }

            $enumAttrs[] = $attr;
        }

        if (count($enumAttrs) === 1) {
            return $enumAttrs[0];
        }

        throw new InvalidArgumentException('Unable to auto-detect enum attribute; please specify explicitly');
    }

    private function resolveAttributeForEnum(Model $model, string $enumClass): string
    {
        foreach ($model->getCasts() as $attr => $cast) {
            if ($cast === $enumClass) {
                return (string) $attr;
            }
        }

        throw new InvalidArgumentException('No attribute cast found for enum '.$enumClass);
    }
}
