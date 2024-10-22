<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param resource|mixed $buffer
     */
    public static function expectedResource(/*resource*/ $buffer): self
    {
        return new self(
            \sprintf('The buffer is not a resource (%s given).', \get_debug_type($buffer))
        );
    }

    public static function invalidTarget(string $target): self
    {
        return new self(
            \sprintf('The target "%s" is not a valid target URL.', $target)
        );
    }

    public static function invalidTimeout(int $timeout): self
    {
        return new self(
            \sprintf('The timeout "%d" is not a valid timeout.', $timeout)
        );
    }
}
