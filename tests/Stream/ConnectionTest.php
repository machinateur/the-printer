<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Stream;

use Machinateur\ThePrinter\Exception\ConnectionException;
use Machinateur\ThePrinter\Exception\InvalidArgumentException;
use Machinateur\ThePrinter\Exception\UnexpectedValueException;
use Machinateur\ThePrinter\Stream\Connection;
use Machinateur\ThePrinter\Tests\Contract\ExampleJsonObject;
use Machinateur\ThePrinter\Tests\Server\ServerTestCase;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * @covers \Machinateur\ThePrinter\Stream\Connection
 *
 * @covers \Machinateur\ThePrinter\Behaviour\ValidateTargetTrait
 * @covers \Machinateur\ThePrinter\Behaviour\ValidateTimeoutTrait
 *
 * @covers \Machinateur\ThePrinter\Exception\ConnectionException
 * @covers \Machinateur\ThePrinter\Exception\InvalidArgumentException
 * @covers \Machinateur\ThePrinter\Exception\UnexpectedValueException
 */
class ConnectionTest extends ServerTestCase
{
    // TODO: Test exception cases.

    private const MARKER_CONNECTION_MAKE = 'connection.make';

    public static function registerRoutes(RouteCollectorProxyInterface $group): void
    {
        // Path: '/tests/connection/echo'; Name: 'echo'
        $group->post('/echo', static function (Request $request, Response $response, array $args): Response {
            // Simply return the request content as response.
            $response->getBody()
                ->write(
                    $request->getBody()
                        ->getContents(),
                );

            return $response;
        });
        // Path: '/tests/connection/sleep'; Name: 'echo'
        $group->post('/sleep', static function (Request $request, Response $response, array $args): Response {
            \usleep(1_500_000);

            $response->getBody()
                ->write('sleep');

            return $response;
        });

        // Path: '/tests/connection/make'; Name: 'make'
        $group->post('/make', static function (Request $request, Response $response, array $args): Response {
            // Return a constant value for the assertion.
            $response->getBody()
                ->write(self::MARKER_CONNECTION_MAKE);

            return $response;
        });
    }

    public function testConstructorCall(): void
    {
        $connection = new Connection($target = 'http://localhost:3000');

        $reflectionProperty_handle = new \ReflectionProperty(Connection::class, 'handle');
        $reflectionProperty_buffer = new \ReflectionProperty(Connection::class, 'buffer');

        self::assertInstanceOf(\CurlHandle::class, $handle = $reflectionProperty_handle->getValue($connection));
        self::assertSame($target, \curl_getinfo($handle, \CURLINFO_EFFECTIVE_URL));

        self::assertNull($reflectionProperty_buffer->getValue($connection));
    }

    public function testConstructorCallWithInvalidTarget(): void
    {
        $target = 'asdf';

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::invalidTarget($target)
                ->getMessage()
        );

        new Connection($target);

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

        new Connection('http://localhost:3000/', $timeout);

        self::fail('Exception should have been thrown.');
    }

    public function testSetPayload(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('echo'));
        $buffer     = \fopen('php://memory', 'w');

        $connection->payload(
            $configuration = new ExampleJsonObject(),
            $content       = 'example content',
        );
        $connection->make($buffer);

        $actualJson   = \stream_get_contents($buffer);
        $time         = (\json_decode($actualJson, true) ?: [])['time'] ?? -1;
        $expectedJson = \json_encode([
            'configuration' => $configuration,
            'content'       => $content,
            'time'          => $time,
        ], \JSON_PRETTY_PRINT | \JSON_PRESERVE_ZERO_FRACTION);

        \fclose($buffer);

        self::assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    public function testSetPayloadInactive(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('echo'));
        $connection->done();

        self::expectException(ConnectionException::class);
        self::expectExceptionMessage(
            ConnectionException::connectionInactive()
                ->getMessage()
        );

        $connection->payload(
            new ExampleJsonObject(),
            'example content',
        );

        self::fail('Exception should have been thrown.');
    }

    public function testCheckActiveConnection(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('echo'));

        self::assertTrue($connection->isActive());

        $connection->done();

        self::assertFalse($connection->isActive());
    }

    public function testMakeConnection(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('make'));
        $buffer     = \fopen('php://memory', 'w');

        $connection->payload(new ExampleJsonObject(), 'test')
            ->make($buffer);

        $actualMarker   = \stream_get_contents($buffer);
        $expectedMarker = self::MARKER_CONNECTION_MAKE;

        \fclose($buffer);

        self::assertSame($expectedMarker, $actualMarker);
    }

    public function testMakeConnectionInactive(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('make'));
        $connection->done();

        self::expectException(ConnectionException::class);
        self::expectExceptionMessage(
            ConnectionException::connectionInactive()
                ->getMessage()
        );

        try {
            $buffer = \fopen('php://memory', 'w');

            $connection->make($buffer);
        } finally {
            \fclose($buffer);
        }

        self::fail('Exception should have been thrown.');
    }

    public function testMakeConnectionInvalidBuffer(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('make'));
        $buffer     = 'fake';

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            InvalidArgumentException::expectedResource($buffer)
                ->getMessage()
        );

        $connection->payload(new ExampleJsonObject(), 'test')
            ->make($buffer);

        self::fail('Exception should have been thrown.');
    }

    public function testMakeConnectionInvalidPort(): void
    {
        $connection = new Connection($target = 'http://localhost:3001');
        $buffer     = \fopen('php://memory', 'w');

        self::expectException(ConnectionException::class);
        self::expectExceptionMessage(
            ConnectionException::curlError(7, 'Failed to connect to localhost port 3001 after 0 ms: Could not connect to server')
                ->getMessage()
        );

        $connection->payload(new ExampleJsonObject(), 'test')
            ->make($buffer);

        self::fail('Exception should have been thrown.');
    }

    public function testMakeConnectionTimeout(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('sleep'), 1);
        $buffer     = \fopen('php://memory', 'w');

        self::expectException(ConnectionException::class);

        try {
            $connection->payload(new ExampleJsonObject(), 'test')
                ->make($buffer);
        } catch (ConnectionException $exception) {
            if (1 !== \preg_match('/after ([0-9]{4}) milliseconds/', $exception->getMessage(), $match, \PREG_UNMATCHED_AS_NULL)) {
                self::fail('Invalid exception message: ' . $exception->getMessage());
            }

            self::expectExceptionMessage(
                ConnectionException::curlError(28, \sprintf('Operation timed out after %d milliseconds with 0 bytes received', $match[1]))
                    ->getMessage()
            );

            throw $exception;
        }

        self::fail('Exception should have been thrown.');
    }

    public function testReuseConnection(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('make'));
        $buffer     = \fopen('php://memory', 'w');

        for ($i = 0; $i < 3; $i++) {
            $position = \ftell($buffer);

            $connection->payload($configuration ??= new ExampleJsonObject(), $content ??= 'test')
                ->make($buffer);

            // Go forward, for the next loop (normally this would be an fread()).
            \fseek($buffer, $position + \strlen(self::MARKER_CONNECTION_MAKE));

            if (2 !== $i) {
                // Add separator.
                \fwrite($buffer, ',', 1);
            }
        }

        // Go back to the beginning.
        \fseek($buffer, 0);

        $actualMarker   = \stream_get_contents($buffer);
        $expectedMarker = \implode(',', \array_fill(0, $i, self::MARKER_CONNECTION_MAKE));

        \fclose($buffer);

        self::assertSame($expectedMarker, $actualMarker);
    }

    public function testParseTarget(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . ($path = static::getRoutePath('make')));

        self::assertSame($path, $connection->getTarget());
    }

    public function testParseTargetInactive(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . ($path = static::getRoutePath('make')));
        $connection->done();

        self::expectException(ConnectionException::class);
        self::expectExceptionMessage(
            ConnectionException::connectionInactive()
                ->getMessage()
        );

        $connection->getTarget();
    }

    public function testParseTargetNoPath(): void
    {
        $connection = new Connection($target = 'http://localhost:3000');

        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage(
            UnexpectedValueException::targetPathUnknown()
                ->getMessage()
        );

        $connection->getTarget();
    }

    public function testCloseConnection(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('echo'));

        $reflectionProperty_handle = new \ReflectionProperty(Connection::class, 'handle');

        self::assertInstanceOf(\CurlHandle::class, $handle = $reflectionProperty_handle->getValue($connection));
        self::assertSame($target, \curl_getinfo($handle, \CURLINFO_EFFECTIVE_URL));

        $connection->done();

        self::assertNull($reflectionProperty_handle->getValue($connection));
    }

    public function testCloseConnectionInactive(): void
    {
        $connection = new Connection($target = 'http://localhost:3000' . static::getRoutePath('echo'));
        $connection->done();

        self::expectException(ConnectionException::class);
        self::expectExceptionMessage(
            ConnectionException::connectionInactive()
                ->getMessage()
        );

        $connection->done();

        self::fail('Exception should have been thrown.');
    }
}
