<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Server;

use Slim\Interfaces\RouteCollectorProxyInterface;

interface ServerRouteProviderInterface
{
    /**
     * Set up the routes for the background server process. Note, that in oder to make routes available,
     *  the test class has to be listed in {@see Server::TESTS} (for now).
     */
    public static function registerRoutes(RouteCollectorProxyInterface $group): void;
}
