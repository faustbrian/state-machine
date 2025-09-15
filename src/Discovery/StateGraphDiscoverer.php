<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Discovery;

use Cline\StateMachine\Attributes\UseStateGraph;
use ReflectionAttribute;
use ReflectionClass;
use Throwable;

use function base_path;
use function class_exists;
use function is_file;
use function mb_rtrim;
use function str_ends_with;
use function str_replace;
use function str_starts_with;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.4
 *
 * @psalm-immutable
 */
final readonly class StateGraphDiscoverer
{
    public function __construct() {}

    /**
     * Discover enums annotated with #[UseStateGraph(Graph::class)].
     *
     * @return array<class-string, class-string> enum => graph class
     */
    public function discover(): array
    {
        $paths = [base_path('src')];

        $classmapFile = base_path('vendor/composer/autoload_classmap.php');

        if (!is_file($classmapFile)) {
            return [];
        }

        /** @var array<class-string, string> $classmap */
        $classmap = require $classmapFile;

        $results = [];

        foreach ($classmap as $class => $file) {
            if (!self::pathIncluded($file, $paths)) {
                continue;
            }

            // Avoid loading vendor or compiled stubs; restrict to PHP files in app
            if (!str_ends_with($file, '.php')) {
                continue;
            }

            // Trigger autoload; Reflection requires the class to be loadable
            try {
                if (!class_exists($class)) {
                    continue;
                }

                $rc = new ReflectionClass($class);

                if (!$rc->isEnum()) {
                    continue;
                }

                $attrs = $rc->getAttributes(UseStateGraph::class, ReflectionAttribute::IS_INSTANCEOF);

                if ($attrs === []) {
                    continue;
                }

                foreach ($attrs as $attr) {
                    /** @var UseStateGraph $instance */
                    $instance = $attr->newInstance();
                    $results[$class] = $instance->graph;
                }
            } catch (Throwable) {
                // Skip problematic classes
                continue;
            }
        }

        return $results;
    }

    /**
     * @param list<string> $paths
     */
    private static function pathIncluded(string $file, array $paths): bool
    {
        $file = str_replace('\\', '/', $file);

        foreach ($paths as $p) {
            $p = str_replace('\\', '/', $p);

            if (str_starts_with($file, mb_rtrim($p, '/').'/')) {
                return true;
            }
        }

        return false;
    }
}
