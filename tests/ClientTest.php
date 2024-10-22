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

namespace Machinateur\ThePrinter\Tests;

use Machinateur\ThePrinter\Client;
use Machinateur\ThePrinter\Configuration\DocumentConfiguration;
use Machinateur\ThePrinter\Configuration\ImageConfiguration;
use Machinateur\ThePrinter\Exception\InvalidArgumentException;
use Machinateur\ThePrinter\Stream\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Machinateur\ThePrinter\Client
 *
 * @covers \Machinateur\ThePrinter\Behaviour\ValidateTargetTrait
 * @covers \Machinateur\ThePrinter\Behaviour\ValidateTimeoutTrait
 *
 * @covers \Machinateur\ThePrinter\Exception\InvalidArgumentException
 */
class ClientTest extends TestCase
{
    // TODO: Compare PDF/image results as shown in https://karlomikus.com/blog/compare-pdf-files-using-php-and-imagemagick.

    // TODO: Implement test.
    //  - Reflection to test protected/private methods  -> https://stackoverflow.com/a/31931510
    //  - Using PHPT for testing                        -> https://codereview.stackexchange.com/a/63595
    //      - Using PHPT with PhpUnit                       -> https://moxio.com/blog/start-testing-with-phpt-tests-in-phpunit/
    //  - Using Mockery to test hard dependencies       -> https://docs.mockery.io/en/latest/cookbook/mocking_hard_dependencies.html
    //  - Testing with multiple PHP versions in docker  -> https://www.shiphp.com/blog/testing-multiple-versions-of-php
    //      - The official PHP docker image                 -> https://hub.docker.com/_/php

    public function testConstructorCall(): void
    {
        $client = new Client($targetBase = 'http://localhost:3000/');

        $reflectionProperty_targetBase   = new \ReflectionProperty(Client::class, 'targetBase');
        $reflectionProperty_timeout      = new \ReflectionProperty(Client::class, 'timeout');

        self::assertSame($targetBase, $reflectionProperty_targetBase->getValue($client));
        self::assertSame(10, $reflectionProperty_timeout->getValue($client));
    }

    public function testConstructorCallWithInvalidTarget(): void
    {
        $targetBase = 'asdf';

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::invalidTarget($targetBase)
                ->getMessage()
        );

        new Client($targetBase);

        self::fail('Exception should have been thrown.');
    }

    public function testConstructorCallWithInvalidTimeout(): void
    {
        $timeout = -1;

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::invalidTimeout($timeout)
                ->getMessage()
        );

        new Client('http://localhost:3000/', $timeout);

        self::fail('Exception should have been thrown.');
    }

    public function testRequestDocumentBinary(): void
    {
        $data = 'test.document.binary';

        $client = new ClientStub($this->getConnectionMock($data), 'http://localhost:3000/');

        self::assertSame($data, $client->documentBinary(new DocumentConfiguration(), ''));
    }

    public function testRequestDocument(): void
    {
        $data = 'test.document';

        $client = new ClientStub($this->getConnectionMock($data), 'http://localhost:3000/');

        /** @var resource $buffer */
        $buffer = (new \ReflectionMethod(Client::class, 'getBuffer'))
            ->invoke($client);

        self::assertSame($buffer, $client->document(new DocumentConfiguration(), '', $buffer));
        self::assertSame($data, \stream_get_contents($buffer));
    }

    public function testRequestDocumentInvalidBuffer(): void
    {
        $data = 'test.document';

        $client = new ClientStub($this->createMock(Connection::class), 'http://localhost:3000/');
        $buffer = 'fake';

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::expectedResource($buffer)
                ->getMessage()
        );

        $client->document(new DocumentConfiguration(), $data, $buffer);

        self::fail('Exception should have been thrown.');
    }

    public function testRequestImageBinary(): void
    {
        $data = 'test.image.binary';

        $client = new ClientStub($this->getConnectionMock($data), 'http://localhost:3000/');

        self::assertSame($data, $client->imageBinary(new ImageConfiguration(), $data));
    }

    public function testRequestImage(): void
    {
        $data = 'test.image';

        $client = new ClientStub($this->getConnectionMock($data), 'http://localhost:3000/');

        /** @var resource $buffer */
        $buffer = (new \ReflectionMethod(Client::class, 'getBuffer'))
            ->invoke($client);

        self::assertSame($buffer, $client->image(new ImageConfiguration(), '', $buffer));
        self::assertSame($data, \stream_get_contents($buffer));
    }

    public function testRequestImageInvalidBuffer(): void
    {
        $data = 'test.image';

        $client = new ClientStub($this->createMock(Connection::class), 'http://localhost:3000/');
        $buffer = 'fake';

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::expectedResource($buffer)
                ->getMessage()
        );

        $client->image(new ImageConfiguration(), $data, $buffer);

        self::fail('Exception should have been thrown.');
    }

    public function testCreateBuffer(): void
    {
        $client = new Client('http://localhost:3000/');

        $reflectionMethod_getBuffer = new \ReflectionMethod(Client::class, 'getBuffer');
        self::assertIsResource($reflectionMethod_getBuffer->invoke($client));
    }

    public function testStreamBufferContent(): void
    {
        $data   = 'test';
        $client = new Client('http://localhost:3000/');

        $reflectionMethod_getBuffer = new \ReflectionMethod(Client::class, 'getBuffer');
        $buffer = $reflectionMethod_getBuffer->invoke($client);

        \fwrite($buffer, $data);
        \rewind($buffer);

        $reflectionMethod_getBufferContent = new \ReflectionMethod(Client::class, 'getBufferContent');
        self::assertSame($data, $reflectionMethod_getBufferContent->invoke($client, $buffer));

        // Error case below...

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::expectedResource($buffer)
                ->getMessage()
        );

        $reflectionMethod_getBufferContent->invoke($client, $buffer);

        self::fail('Exception should have been thrown.');
    }

    public function testRefreshConnection(): void
    {
        $client = new Client($targetBase = 'http://localhost:3000/');

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::exactly(2))
            ->method('isActive')
            ->willReturn(false);

        $reflectionProperty_connection = new \ReflectionProperty(Client::class, 'connection');
        $reflectionProperty_connection->setValue($client, $connectionMock);

        $reflectionMethod_getConnection = new \ReflectionMethod(Client::class, 'getConnection');
        $connection = $reflectionMethod_getConnection->invoke($client, '/document');

        self::assertInstanceOf(Connection::class, $connection);
        //$connection->done();

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::exactly(2))
            ->method('isActive')
            ->willReturn(true);
        $connectionMock->expects(self::once())
            ->method('getTarget')
            ->willReturn($connection->getTarget());

        $reflectionProperty_connection = new \ReflectionProperty(Client::class, 'connection');
        $reflectionProperty_connection->setValue($client, $connectionMock);

        $reflectionMethod_getConnection = new \ReflectionMethod(Client::class, 'getConnection');
        $connection = $reflectionMethod_getConnection->invoke($client, '/image');

        self::assertInstanceOf(Connection::class, $connection);
        $connection->done();
    }

    /**
     * Create a mock for {@see Connection} that allows for one single "request" procedure to occur.
     *
     * The use of this method is limited to {@see ClientStub}.
     *
     * @return Connection&MockObject
     */
    private function getConnectionMock(string $data): Connection
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::once())
            ->method('payload')
            ->willReturnSelf();
        $connectionMock->expects(self::once())
            ->method('make')
            ->with(
                self::callback(static function (/*resource*/ $buffer) use ($data): bool {
                    \fwrite($buffer, $data);
                    \rewind($buffer);

                    return true;
                })
            )
            ->willReturnSelf();
        $connectionMock->expects(self::once())
            ->method('done');

        return $connectionMock;
    }
}
