<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Contract;

abstract class JsonObject implements JsonObjectInterface
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }
}
