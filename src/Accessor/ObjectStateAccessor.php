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
            /** @var object $value */
            return $subject->getAttribute($attribute);
        }

        // Try public property first
        if (property_exists($subject, $attribute)) {
            /** @var object $value */
            return $subject->{$attribute};
        }

        // Try getter method
        $getter = self::accessorName('get', $attribute);

        if (method_exists($subject, $getter)) {
            /** @var object $value */
            return $subject->{$getter}();
        }

        // Try reflection on non-public property
        $rc = new ReflectionClass($subject);

        if ($rc->hasProperty($attribute)) {
            $prop = $rc->getProperty($attribute);
            self::ensureReadable($prop);

            /** @var object $value */
            return $prop->getValue($subject);
        }

        throw new InvalidArgumentException("State attribute '{$attribute}' not found on subject of type ".$subject::class);
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

        $setter = self::accessorName('set', $attribute);

        if (method_exists($subject, $setter)) {
            $subject->{$setter}($state);

            return;
        }

        $rc = new ReflectionClass($subject);

        if ($rc->hasProperty($attribute)) {
            $prop = $rc->getProperty($attribute);
            self::ensureWritable($prop);
            $prop->setValue($subject, $state);

            return;
        }

        throw new InvalidArgumentException("Cannot set state attribute '{$attribute}' on subject of type ".$subject::class);
    }

    public function persist(object $subject): void
    {
        if ($subject instanceof Model) {
            $subject->save();
        }
        // Otherwise no-op for non-persistent subjects
    }

    private static function accessorName(string $prefix, string $attribute): string
    {
        return $prefix.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $attribute)));
    }

    private static function ensureReadable(ReflectionProperty $prop): void
    {
        if (!$prop->isPublic()) {
            $prop->setAccessible(true);
        }
    }

    private static function ensureWritable(ReflectionProperty $prop): void
    {
        if (!$prop->isPublic()) {
            $prop->setAccessible(true);
        }
    }
}
