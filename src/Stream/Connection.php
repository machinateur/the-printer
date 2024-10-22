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

namespace Machinateur\ThePrinter\Stream;

use Machinateur\ThePrinter\Behaviour\ValidateTargetTrait;
use Machinateur\ThePrinter\Behaviour\ValidateTimeoutTrait;
use Machinateur\ThePrinter\Contract\JsonObject;
use Machinateur\ThePrinter\Exception\ConnectionException;
use Machinateur\ThePrinter\Exception\InvalidArgumentException;
use Machinateur\ThePrinter\Exception\UnexpectedValueException;

class Connection
{
    use ValidateTargetTrait;
    use ValidateTimeoutTrait;

    private ?\CurlHandle $handle;

    /**
     * @var resource|null
     */
    private $buffer;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $target, int $timeout = 10)
    {
        $this->validateTarget($target);
        $this->validateTimeout($timeout);

        $this->handle = \curl_init();

        \curl_setopt($this->handle, \CURLOPT_URL, $target);
        \curl_setopt($this->handle, \CURLOPT_POST, true);
        \curl_setopt($this->handle, \CURLOPT_POSTFIELDS, null);
        // TODO: Introduce MS-unit timeout support.
        \curl_setopt($this->handle, \CURLOPT_TIMEOUT, $timeout);
        \curl_setopt($this->handle, \CURLOPT_WRITEFUNCTION, $this);
        \curl_setopt($this->handle, \CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $this->buffer = null;
    }

    /**
     * @param \CurlHandle $handle
     */
    public function __invoke(/*resource*/ $handle, string $data): int
    {
        // Failsafe, but generally the buffer is set before invocation.
        if (null === $this->buffer) {
            return -1; // @codeCoverageIgnore
        }

        return false !== ($write = \fwrite($this->buffer, $data))
            ? $write
            : -1 // Nothing written.
            ;
    }

    public function payload(JsonObject $configuration, string $content): self
    {
        if (!$this->isActive()) {
            throw ConnectionException::connectionInactive();
        }
        \assert($this->handle instanceof \CurlHandle);

        $payloadObject = [
            'configuration' => $configuration,
            'content'       => $content,
            'time'          => \time(),
        ];
        // TODO: Handle exception when encoding (coming from unknown contents of JsonObject).
        $payload = \json_encode($payloadObject, \JSON_PRETTY_PRINT | \JSON_PRESERVE_ZERO_FRACTION);

        \curl_setopt($this->handle, \CURLOPT_POSTFIELDS, $payload);

        return $this;
    }

    /**
     * @param resource $buffer
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws ConnectionException
     */
    public function make(/*resource*/ $buffer): self
    {
        if (!$this->isActive()) {
            throw ConnectionException::connectionInactive();
        }
        \assert($this->handle instanceof \CurlHandle);

        if (false === \is_resource($buffer)) {
            throw InvalidArgumentException::expectedResource($buffer);
        }

        // Get the initial stream position.
        $position = \ftell($buffer);
        if (false === $position) {
            throw UnexpectedValueException::bufferPosition($buffer); // @codeCoverageIgnore
        }

        // Set the internal buffer reference.
        $this->buffer = $buffer;

        // Execute the handle, which will call the assigned write function ($this::__invoke()).
        \curl_exec($this->handle);

        if ($errno = \curl_errno($this->handle)) {
            $error = \curl_error($this->handle);

            throw ConnectionException::curlError($errno, $error);
        }

        // Remove the internal buffer reference, thus make this connection inactive.
        $this->buffer = null;

        // Rewind to the initial stream position.
        $position = \fseek($buffer, $position);
        if (-1 === $position) {
            throw UnexpectedValueException::bufferPosition($buffer, $position); // @codeCoverageIgnore
        }

        return $this;
    }

    public function done(): void
    {
        if (!$this->isActive()) {
            throw ConnectionException::connectionInactive();
        }
        \assert($this->handle instanceof \CurlHandle);

        \curl_close($this->handle);

        $this->handle = null;
    }

    /**
     * @throws ConnectionException
     * @throws UnexpectedValueException
     */
    public function getTarget(): string
    {
        if (!$this->isActive()) {
            throw ConnectionException::connectionInactive();
        }
        \assert($this->handle instanceof \CurlHandle);

        $target = \curl_getinfo($this->handle, \CURLINFO_EFFECTIVE_URL);

        // @phpstan-ignore-next-line
        if (false === $target) {
            throw ConnectionException::targetUnknown(); // @codeCoverageIgnore
        }

        $target = \parse_url($target, \PHP_URL_PATH);

        if (null === $target || false === $target) {
            throw UnexpectedValueException::targetPathUnknown();
        }

        return $target;
    }

    public function isActive(): bool
    {
        return null !== $this->handle;
    }
}
