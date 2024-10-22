<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Contract;

interface JsonObjectInterface extends \JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
