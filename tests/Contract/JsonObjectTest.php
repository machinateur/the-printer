<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Contract;

use Machinateur\ThePrinter\Contract\JsonObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Machinateur\ThePrinter\Contract\JsonObject
 */
class JsonObjectTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $object = new ExampleJsonObject();

        self::assertJsonStringEqualsJsonString(
            \json_encode([
                'property1' => 'value1',
                'property2' => 'value2',
                'property3' => 'value3',
            ]),
            \json_encode($object),
        );
    }
}
