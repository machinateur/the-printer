<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Behaviour;

use Machinateur\ThePrinter\Exception\InvalidArgumentException;

trait ValidateTimeoutTrait
{
    /**
     * @throws InvalidArgumentException
     */
    private function validateTimeout(int $timeout): void
    {
        if (0 > $timeout) {
            throw InvalidArgumentException::invalidTimeout($timeout);
        }
    }
}
