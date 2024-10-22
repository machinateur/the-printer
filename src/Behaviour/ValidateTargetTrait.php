<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Behaviour;

use Machinateur\ThePrinter\Exception\InvalidArgumentException;

trait ValidateTargetTrait
{
    /**
     * @throws InvalidArgumentException
     */
    private function validateTarget(string $target): void
    {
        if (false === \filter_var($target, \FILTER_VALIDATE_URL)) {
            throw InvalidArgumentException::invalidTarget($target);
        }
    }
}
