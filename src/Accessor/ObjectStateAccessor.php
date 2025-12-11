<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Accessor;

use Cline\StateMachine\Contracts\StateAccessorInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

use function method_exists;
use function property_exists;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class ObjectStateAccessor implements StateAccessorInterface
{
    public function get(object $subject, string $attribute): object
    {
        if ($subject instanceof Model) {
            return $subject->getAttribute($attribute);
        }

        // Try public property first
        if (property_exists($subject, $attribute)) {
            return $subject->{$attribute};
        }

        // Try getter method
        $getter = $this->accessorName('get', $attribute);

        if (method_exists($subject, $getter)) {
            return $subject->{$getter}();
        }

        // Try reflection on non-public property
        $rc = new ReflectionClass($subject);

        if ($rc->hasProperty($attribute)) {
            $prop = $rc->getProperty($attribute);
            $this->ensureReadable($prop);

            return $prop->getValue($subject);
        }

        throw new InvalidArgumentException(sprintf("State attribute '%s' not found on subject of type ", $attribute).$subject::class);
    }

    public function set(object $subject, string $attribute, object $state): void
    {
        if ($subject instanceof Model) {
            $subject->setAttribute($attribute, $state);

            return;
        }

        if (property_exists($subject, $attribute)) {
            $subject->{$attribute} = $state;

            return;
        }

        $setter = $this->accessorName('set', $attribute);

        if (method_exists($subject, $setter)) {
            $subject->{$setter}($state);

            return;
        }

        $rc = new ReflectionClass($subject);

        if ($rc->hasProperty($attribute)) {
            $prop = $rc->getProperty($attribute);
            $this->ensureWritable($prop);
            $prop->setValue($subject, $state);

            return;
        }

        throw new InvalidArgumentException(sprintf("Cannot set state attribute '%s' on subject of type ", $attribute).$subject::class);
    }

    public function persist(object $subject): void
    {
        if ($subject instanceof Model) {
            $subject->save();
        }

        // Otherwise no-op for non-persistent subjects
    }

    private function accessorName(string $prefix, string $attribute): string
    {
        return $prefix.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $attribute)));
    }

    private function ensureReadable(ReflectionProperty $prop): void
    {
        if ($prop->isPublic()) {
            return;
        }
    }

    private function ensureWritable(ReflectionProperty $prop): void
    {
        if ($prop->isPublic()) {
            return;
        }
    }
}
