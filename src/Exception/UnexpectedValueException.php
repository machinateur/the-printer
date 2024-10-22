<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Exception;

class UnexpectedValueException extends \UnexpectedValueException
{
    /**
     * @codeCoverageIgnore
     *
     * @param resource $buffer
     */
    public static function bufferPosition(/*resource*/ $buffer, int|false $position = false): self
    {
        return new self(\sprintf('The buffer position %d is invalid.', $position));
    }

    public static function targetPathUnknown(): self
    {
        return new self('The target path is unknown.');
    }
}
