<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\StateMachine\Registry;

use Cline\StateMachine\Discovery\StateGraphDiscoverer;
use Cline\StateMachine\StateGraph;
use Cline\StateMachine\StateGraphBuilder;

use function array_key_exists;
use function config;
use function file_put_contents;
use function is_array;
use function is_file;
use function unlink;
use function var_export;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @version 1.0.5
 */
final class StateGraphRegistry
{
    /** @var array<class-string, StateGraph> */
    private array $cache = [];

    public function __construct() {}

    public function resolve(string $enumClass): StateGraph
    {
        if (array_key_exists($enumClass, $this->cache)) {
            return $this->cache[$enumClass];
        }

        $compiled = self::loadCompiled();

        if (array_key_exists($enumClass, $compiled)) {
            return $this->cache[$enumClass] = self::rehydrate($enumClass, $compiled[$enumClass]);
        }

        // Discover attributed map class on-demand (dev) when cache miss
        $discoverer = new StateGraphDiscoverer();
        $discovered = $discoverer->discover();
        $mapClass = $discovered[$enumClass] ?? null;

        if ($mapClass !== null) {
            $builder = new StateGraphBuilder($enumClass);
            $mapClass::configure($builder);

            return $this->cache[$enumClass] = $builder->build();
        }

        // Default to allow no transitions if no attributed map exists
        return $this->cache[$enumClass] = new StateGraph($enumClass, [], false, []);
    }

    /**
     * @param array<class-string, array{rules: array<string, array<string, null|string>>, ignore: bool, middleware: list<class-string>}> $compiled
     */
    public function writeCompiled(array $compiled): void
    {
        $path = (string) config('discovery.paths.state_machines');
        $export = var_export($compiled, true);
        file_put_contents($path, "<?php\nreturn {$export};\n");
    }

    /**
     * @return array<class-string, array{rules: array<string, array<string, null|string>>, ignore: bool, middleware: list<class-string>}>
     */
    public function compileAll(): array
    {
        $result = [];

        // Discover and compile all attributed maps
        $discoverer = new StateGraphDiscoverer();
        $discovered = $discoverer->discover(); // enum => map class

        foreach ($discovered as $enumClass => $mapClass) {
            $builder = new StateGraphBuilder($enumClass);
            $mapClass::configure($builder);
            $map = $builder->build();
            $result[$enumClass] = [
                'rules' => $map->rules,
                'ignore' => $map->ignoreSameState,
                'middleware' => $map->middlewares,
                'named' => $map->named,
            ];
            $this->cache[$enumClass] = $map;
        }

        return $result;
    }

    public function clear(): void
    {
        $path = (string) config('discovery.paths.state_machines');

        if (is_file($path)) {
            unlink($path);
        }

        $this->cache = [];
    }

    /**
     * @param array{rules: array<string, array<string, null|string>>, ignore: bool, middleware: list<class-string>} $data
     */
    private static function rehydrate(string $enumClass, array $data): StateGraph
    {
        $named = $data['named'] ?? [];

        return new StateGraph($enumClass, $data['rules'], $data['ignore'], $data['middleware'], $named);
    }

    /**
     * @return array<class-string, array{rules: array<string, array<string, null|string>>, ignore: bool, middleware: list<class-string>}>
     */
    private static function loadCompiled(): array
    {
        $path = (string) config('discovery.paths.state_machines');

        if (is_file($path)) {
            /** @var array<class-string, array{rules: array<string, array<string, null|string>>, ignore: bool, middleware: list<class-string>, named?: array<string, array{from: list<string>, to: string, properties: array<string, mixed>}>}> $data */
            $data = require $path;

            return is_array($data) ? $data : [];
        }

        return [];
    }
}
