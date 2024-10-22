<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests;

use Machinateur\ThePrinter\Client;
use Machinateur\ThePrinter\Stream\Connection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * A client stub that allows to inject a connection (mock) and skip connection refresh.
 *
 * Both, getter and setter, of `$connection` are public for easier inspection.
 *
 * @internal for test purposes only
 */
class ClientStub extends Client
{
    /**
     * @param Connection&MockObject $connection
     */
    public function __construct(
        Connection $connection,
        string     $targetBase = 'http://localhost:3000',
        int        $timeout = 10,
    ) {
        parent::__construct($targetBase, $timeout);

        $this->connection = $connection;
    }

    public function getConnection(string $target): Connection
    {
        return $this->connection;
    }

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }
}
