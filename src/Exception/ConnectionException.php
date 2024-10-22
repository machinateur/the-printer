<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Exception;

class ConnectionException extends \LogicException
{
    public static function connectionInactive(): self
    {
        return new self('The connection is not active.');
    }

    /**
     * @codeCoverageIgnore
     */
    public static function targetUnknown(): self
    {
        return new self('The connection target is unknown.');
    }

    public static function curlError(int $errno, string $error): self
    {
        return new self(
            \sprintf('The connection error is "%s" (curl %d).', $error, $errno)
        );
    }
}
