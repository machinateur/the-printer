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

namespace Machinateur\ThePrinter;

use Machinateur\ThePrinter\Behaviour\ValidateTargetTrait;
use Machinateur\ThePrinter\Behaviour\ValidateTimeoutTrait;
use Machinateur\ThePrinter\Configuration\DocumentConfiguration;
use Machinateur\ThePrinter\Configuration\ImageConfiguration;
use Machinateur\ThePrinter\Contract\JsonObject;
use Machinateur\ThePrinter\Exception\ClientException;
use Machinateur\ThePrinter\Exception\ConnectionException;
use Machinateur\ThePrinter\Exception\InvalidArgumentException;
use Machinateur\ThePrinter\Exception\UnexpectedValueException;
use Machinateur\ThePrinter\Stream\Connection;

class Client
{
    use ValidateTargetTrait;
    use ValidateTimeoutTrait;

    protected const TARGET_DOCUMENT = '/document';
    protected const TARGET_IMAGE    = '/image';

    protected readonly string $targetBase;

    protected readonly int $timeout;

    protected ?Connection $connection = null;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $targetBase, int $timeout = 10)
    {
        $targetBase = \filter_var($targetBase, \FILTER_SANITIZE_URL) ?: '';

        $this->validateTarget($targetBase);
        $this->validateTimeout($timeout);

        $this->targetBase = $targetBase;
        $this->timeout    = $timeout;
    }

    public function documentBinary(
        DocumentConfiguration $configuration,
        string                $content,
    ): string
    {
        return $this->getBufferContent($this->document($configuration, $content));
    }

    /**
     * @param resource|null $buffer
     * @return resource
     */
    public function document(
        DocumentConfiguration $configuration,
        string                $content,
        /*resource*/          $buffer = null,
        bool                  $dispose = true,
    )/*: resource*/
    {
        return $this->request(static::TARGET_DOCUMENT, $configuration, $content, $buffer, $dispose);
    }

    public function imageBinary(
        ImageConfiguration $configuration,
        string             $content
    ): string
    {
        return $this->getBufferContent($this->image($configuration, $content));
    }

    /**
     * @param resource|null $buffer
     * @return resource
     */
    public function image(
        ImageConfiguration $configuration,
        string             $content,
        /*resource*/       $buffer = null,
        bool               $dispose = true,
    )/*: resource*/
    {
        return $this->request(static::TARGET_IMAGE, $configuration, $content, $buffer, $dispose);
    }

    /**
     * @param resource|null $buffer
     * @return resource
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws ConnectionException
     */
    protected function request(
        string       $target,
        JsonObject   $configuration,
        string       $content,
        /*resource*/ $buffer = null,
        bool         $dispose = true,
    )/*: resource*/
    {
        $connection = $this->getConnection($target);

        if (null === $buffer) {
            $buffer = $this->getBuffer();
        } elseif (!\is_resource($buffer)) {
            throw InvalidArgumentException::expectedResource($buffer);
        }

        $connection->payload($configuration, $content)
            ->make($buffer);

        if (true === $dispose) {
            $connection->done();
        }

        return $buffer;
    }

    /**
     * @return resource
     */
    protected function getBuffer()/*: resource*/
    {
        // @phpstan-ignore-next-line
        return \fopen('php://temp', 'w+', false, null);
    }

    /**
     * @param resource $buffer
     * @throws ClientException
     */
    protected function getBufferContent(/*resource*/ $buffer): string
    {
        if (!\is_resource($buffer)) {
            throw InvalidArgumentException::expectedResource($buffer);
        }

        try {
            $content = \stream_get_contents($buffer);

            if (false === $content) {
                throw ClientException::bufferContent($buffer); // @codeCoverageIgnore
            }

            return $content;
        } finally {
            if (\is_resource($buffer)) {
                @\fclose($buffer);
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getConnection(string $target): Connection
    {
        if (null !== $this->connection) {
            if ($this->connection->isActive() && $target !== $this->connection->getTarget()) {
                $this->connection->done();
            }
        }

        if (null === $this->connection || !$this->connection->isActive()) {
            $target = \rtrim($this->targetBase, '/') . '/' . \ltrim($target, '/');

            $this->connection = new Connection($target, $this->timeout);
        }

        return $this->connection;
    }
}
