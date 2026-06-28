<?php
namespace Src\Router;

use Src\Facade\Design;
/**
 * Summary of Route
 * @method static Router get(string $path)
 * @method static Router post(string $path)
 * @method static Router put(string $path)
 * @method static Router delete(string $path)
 * @method static Router group(string $prefix,callable $callback,array | string $middleware)
 * @method static array route()
 * @method static void globalMiddleware(array|string $middleware,string $method = "ALL")
 * @mixin Router
 */
final class Route extends Design{
    protected function getProcessClass(): string{
        return Router::class;
    }
}