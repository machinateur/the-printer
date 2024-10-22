<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Server;

use Machinateur\ThePrinter\Tests\ProcessTestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;

/**
 * A test case base class, where the test requires PHP built-in server running.
 *  The process is automatically managed while the test case is executed.
 *
 * @see ProcessTestCase
 */
abstract class ServerTestCase extends ProcessTestCase implements ServerRouteProviderInterface
{
    abstract public static function registerRoutes(RouteCollectorProxyInterface $group): void;

    /**
     * Initialization of the server process object for managing the background server.
     *  Note, this will be called in the PhpUnit process, not the actual server.
     */
    protected static function createProcess(): Server
    {
        return new Server(tests: [static::class]);
    }

    /**
     * Shortcut method for {@see Server::getRoutePathSegment()}.
     */
    protected static function getRoutePathSegment(string $name, bool $prependSlash = false): string
    {
        return Server::getRoutePathSegment($name, $prependSlash);
    }

    /**
     * Build the complete route path for the given route name in this class.
     *  Note, this requires the route paths in {@see self::registerRoutes()} to be set
     *  using {@see self::getRoutePathSegment()}.
     */
    protected static function getRoutePath(string $name): string
    {
        return \sprintf('/tests/%s/%s',
            static::getRoutePathSegment((new \ReflectionClass(static::class))->getShortName()),
            static::getRoutePathSegment($name),
        );
    }
}
