<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Exception;

/**
 * @codeCoverageIgnore
 */
class ClientException extends \RuntimeException
{
    /**
     * @param resource $buffer
     */
    public static function bufferContent(/*resource*/ $buffer): self
    {
        return new self(\sprintf('The buffer content was %s.', \get_debug_type($buffer)));
    }
}
