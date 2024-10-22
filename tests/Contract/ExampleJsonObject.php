<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Contract;

use Machinateur\ThePrinter\Contract\JsonObject;

/**
 * A {@see JsonObject} "implementation" dedicated for testing `\json_encode()` behaviour.
 *
 * @internal for test purposes only
 */
class ExampleJsonObject extends JsonObject
{
    public $property1 = 'value1';
    public $property2 = 'value2';
    public $property3 = 'value3';
}
