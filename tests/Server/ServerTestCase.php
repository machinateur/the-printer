<?php
/*
 * MIT License
 *
 * Copyright (c) 2020-2024 machinateur
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
