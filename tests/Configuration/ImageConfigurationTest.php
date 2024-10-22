<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Configuration;

use Machinateur\ThePrinter\Configuration\ImageConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Machinateur\ThePrinter\Configuration\ImageConfiguration
 */
class ImageConfigurationTest extends TestCase
{
    private ?ImageConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new ImageConfiguration();
    }

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    public function testType(): void
    {
        self::assertSame(ImageConfiguration::TYPE_PNG, $this->configuration->getType());

        foreach (ImageConfiguration::TYPE_LIST as $type) {
            $this->configuration->setType($type);

            self::assertTrue($this->configuration->isType($type));
        }
        \assert(isset($type));

        $this->configuration->setType('gif');
        self::assertSame($type, $this->configuration->getType());

        $this->configuration->unsetType();

        self::assertFalse($this->configuration->hasType());

        $this->configuration->resetType();

        self::assertSame(ImageConfiguration::TYPE_PNG, $this->configuration->getType());
    }

    public function testQuality(): void
    {
        self::assertNull($this->configuration->getQuality());

        $this->configuration->setQuality(200);

        self::assertSame(ImageConfiguration::QUALITY_MAX, $this->configuration->getQuality());

        $this->configuration->setQuality(0);

        self::assertSame(ImageConfiguration::QUALITY_MIN, $this->configuration->getQuality());

        $this->configuration->setQuality(-50);

        self::assertSame(50, $this->configuration->getQuality());

        $this->configuration->unsetQuality();

        self::assertFalse($this->configuration->hasQuality());
    }

    public function testScale(): void
    {
        self::assertNull($this->configuration->getScale());

        $this->configuration->setScale(2);

        self::assertSame(2, $this->configuration->getScale());

        $this->configuration->setScale(0);

        self::assertSame(0, $this->configuration->getScale());

        $this->configuration->setScale(-1);

        self::assertSame(1, $this->configuration->getScale());

        $this->configuration->unsetScale();

        self::assertFalse($this->configuration->hasScale());
    }

    public function testArea(): void
    {
        self::assertNull($this->configuration->getArea());

        $this->configuration->setArea(
            null, // x
            null, // y
            $areaWidth  = 150,
            $areaHeight = 200,
        );

        self::assertSame([
            'x'      => null,
            'y'      => null,
            'width'  => $areaWidth,
            'height' => $areaHeight,
        ], $this->configuration->getArea());

        $this->configuration->unsetArea();

        self::assertFalse($this->configuration->hasArea());
    }

    public function testOptimize(): void
    {
        self::assertFalse($this->configuration->isOptimize());

        $this->configuration->setOptimize(true);

        self::assertTrue($this->configuration->isOptimize());

        $this->configuration->setOptimize(false);

        self::assertFalse($this->configuration->isOptimize());
    }

    public function testCaptureViewportOnly(): void
    {
        self::assertFalse($this->configuration->isCaptureViewportOnly());

        $this->configuration->setCaptureViewportOnly(true);

        self::assertTrue($this->configuration->isCaptureViewportOnly());

        $this->configuration->setCaptureViewportOnly(false);

        self::assertFalse($this->configuration->isCaptureViewportOnly());
    }

    public function testCaptureSurface(): void
    {
        self::assertTrue($this->configuration->isCaptureSurface());

        $this->configuration->setCaptureSurface(false);

        self::assertFalse($this->configuration->isCaptureSurface());

        $this->configuration->setCaptureSurface(true);

        self::assertTrue($this->configuration->isCaptureSurface());
    }

    public function testCapturePage(): void
    {
        self::assertFalse($this->configuration->isCapturePage());

        $this->configuration->setCapturePage(true);

        self::assertTrue($this->configuration->isCapturePage());

        $this->configuration->setCapturePage(false);

        self::assertFalse($this->configuration->isCapturePage());
    }

    public function testDisplayTransparent(): void
    {
        self::assertFalse($this->configuration->isDisplayTransparent());

        $this->configuration->setDisplayTransparent(true);

        self::assertTrue($this->configuration->isDisplayTransparent());

        $this->configuration->setDisplayTransparent(false);

        self::assertFalse($this->configuration->isDisplayTransparent());
    }


    public function testSerialize(): void
    {
        $expectedJson = <<<'JSON'
{
    "type":                "png",
    "quality":             null,
    "scale":               null,
    "area":                null,
    "optimize":            false,
    "captureViewportOnly": false,
    "captureSurface":      true,
    "capturePage":         false,
    "displayTransparent":  false
}
JSON;

        self::assertJsonStringEqualsJsonString($expectedJson, \json_encode($this->configuration, \JSON_PRETTY_PRINT | \JSON_PRESERVE_ZERO_FRACTION));
    }
}