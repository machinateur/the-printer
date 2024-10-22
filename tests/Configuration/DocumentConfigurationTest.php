<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Configuration;

use Machinateur\ThePrinter\Configuration\DocumentConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Machinateur\ThePrinter\Configuration\DocumentConfiguration
 */
class DocumentConfigurationTest extends TestCase
{
    private ?DocumentConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new DocumentConfiguration();
    }

    protected function tearDown(): void
    {
        $this->configuration = null;
    }


    public function testScale(): void
    {
        self::assertSame(DocumentConfiguration::SCALE_DEFAULT, $this->configuration->getScale());

        $this->configuration->setScale(3.0);

        self::assertSame(DocumentConfiguration::SCALE_MAX, $this->configuration->getScale());

        $this->configuration->setScale(0.0);

        self::assertSame(DocumentConfiguration::SCALE_MIN, $this->configuration->getScale());

        $this->configuration->setScale(-1 * DocumentConfiguration::SCALE_DEFAULT);

        self::assertSame(DocumentConfiguration::SCALE_DEFAULT, $this->configuration->getScale());
        self::assertTrue($this->configuration->hasScale());

        $this->configuration->unsetScale();

        self::assertNull($this->configuration->getScale());
        self::assertFalse($this->configuration->hasScale());

        $this->configuration->resetScale();

        self::assertSame(DocumentConfiguration::SCALE_DEFAULT, $this->configuration->getScale());
    }

    public function testDisplayContentOnly(): void
    {
        self::assertFalse($this->configuration->isDisplayContentOnly());

        $this->configuration->setDisplayContentOnly(true);

        self::assertTrue($this->configuration->isDisplayContentOnly());

        $this->configuration->setDisplayContentOnly(false);

        self::assertFalse($this->configuration->isDisplayContentOnly());
    }

    public function testDisplayBackgroundGraphic(): void
    {
        self::assertTrue($this->configuration->isDisplayBackgroundGraphic());

        $this->configuration->setDisplayBackgroundGraphic(false);

        self::assertFalse($this->configuration->isDisplayBackgroundGraphic());

        $this->configuration->setDisplayBackgroundGraphic(true);

        self::assertTrue($this->configuration->isDisplayBackgroundGraphic());
    }

    public function testDisplayTransparent(): void
    {
        self::assertFalse($this->configuration->isDisplayTransparent());

        $this->configuration->setDisplayTransparent(true);

        self::assertTrue($this->configuration->isDisplayTransparent());

        $this->configuration->setDisplayTransparent(false);

        self::assertFalse($this->configuration->isDisplayTransparent());
    }

    public function testTemplate(): void
    {
        self::assertNull($this->configuration->getTemplate());

        $this->configuration->setTemplate([
            'not_defined' => 'content',

            DocumentConfiguration::TEMPLATE_HEADER => 'header',
            DocumentConfiguration::TEMPLATE_FOOTER => 'footer',
        ]);

        self::assertSame('header', $this->configuration->getTemplateHeader());
        self::assertSame('footer', $this->configuration->getTemplateFooter());

        $this->configuration->unsetTemplate();

        self::assertFalse($this->configuration->hasTemplateHeader());
        self::assertFalse($this->configuration->hasTemplateFooter());
        self::assertFalse($this->configuration->hasTemplate());

        $this->configuration->setTemplateHeader('header 2');

        self::assertTrue($this->configuration->hasTemplateHeader());
        self::assertFalse($this->configuration->hasTemplateFooter());
        self::assertTrue($this->configuration->hasTemplate());

        $this->configuration->unsetTemplateHeader();
        $this->configuration->setTemplateFooter('footer 2');

        self::assertFalse($this->configuration->hasTemplateHeader());
        self::assertTrue($this->configuration->hasTemplateFooter());
        self::assertTrue($this->configuration->hasTemplate());

        $this->configuration->unsetTemplateFooter();

        self::assertFalse($this->configuration->hasTemplate());
    }

    public function testPageOrientation(): void
    {
        self::assertSame(DocumentConfiguration::ORIENTATION_PORTRAIT, $this->configuration->getPageOrientation());

        $this->configuration->setPageOrientation('scrambled');

        self::assertFalse($this->configuration->isPageOrientation('scrambled'));
        self::assertTrue($this->configuration->isPageOrientation(DocumentConfiguration::ORIENTATION_PORTRAIT));
        self::assertFalse($this->configuration->isPageOrientation(DocumentConfiguration::ORIENTATION_LANDSCAPE));

        $this->configuration->setPageOrientation(DocumentConfiguration::ORIENTATION_LANDSCAPE);

        self::assertFalse($this->configuration->isPageOrientation(DocumentConfiguration::ORIENTATION_PORTRAIT));
        self::assertTrue($this->configuration->isPageOrientation(DocumentConfiguration::ORIENTATION_LANDSCAPE));

        $this->configuration->resetPageOrientation();

        self::assertSame(DocumentConfiguration::ORIENTATION_PORTRAIT, $this->configuration->getPageOrientation());
    }

    public function testPageFormat(): void
    {
        self::assertSame(DocumentConfiguration::FORMAT_A4, $this->configuration->getPageFormat());

        foreach (DocumentConfiguration::FORMAT_LIST as $format) {
            $this->configuration->setPageFormat($format);

            self::assertTrue($this->configuration->isPageFormat($format));
        }
        \assert(isset($format));

        $this->configuration->setPageFormat('unknown');
        self::assertSame($format, $this->configuration->getPageFormat());

        $this->configuration->unsetPageFormat();

        self::assertNull($this->configuration->getPageFormat());

        $this->configuration->resetPageFormat();

        self::assertSame(DocumentConfiguration::FORMAT_A4, $this->configuration->getPageFormat());
    }

    public function testPageWidth(): void
    {
        self::assertNull($this->configuration->getPageWidth());

        $this->configuration->setPageWidth(800);

        self::assertTrue($this->configuration->hasPageWidth());
        self::assertSame(800, $this->configuration->getPageWidth());

        $this->configuration->setPageWidth(-800);

        self::assertSame(800, $this->configuration->getPageWidth());

        $this->configuration->setPageWidth('800px');

        self::assertSame('800px', $this->configuration->getPageWidth());

        $this->configuration->unsetPageWidth();

        self::assertFalse($this->configuration->hasPageWidth());
    }

    public function testPageHeight(): void
    {
        self::assertNull($this->configuration->getPageHeight());

        $this->configuration->setPageHeight(600);

        self::assertTrue($this->configuration->hasPageHeight());
        self::assertSame(600, $this->configuration->getPageHeight());

        $this->configuration->setPageHeight(-600);

        self::assertSame(600, $this->configuration->getPageHeight());

        $this->configuration->setPageHeight('600px');

        self::assertSame('600px', $this->configuration->getPageHeight());

        $this->configuration->unsetPageHeight();

        self::assertFalse($this->configuration->hasPageHeight());
    }

    public function testPageRange(): void
    {
        self::assertSame('', $this->configuration->getPageRange());

        foreach (['1-3', 'odd'] as $pageRange) {
            $this->configuration->setPageRange($pageRange);

            self::assertSame($pageRange, $this->configuration->getPageRange());
        }

        $this->configuration->unsetPageRange();

        self::assertFalse($this->configuration->hasPageRange());
    }

    public function testPageOverride(): void
    {
        self::assertTrue($this->configuration->isPageOverride());

        $this->configuration->setPageOverride(false);

        self::assertFalse($this->configuration->isPageOverride());

        $this->configuration->setPageOverride(true);

        self::assertTrue($this->configuration->isPageOverride());
    }

    public function testMargin(): void
    {
        self::assertNull($this->configuration->getMargin());

        $this->configuration->setMargin(
            marginTop:    10,
            marginBottom: 10,
        );

        self::assertTrue($this->configuration->hasMargin());

        $this->configuration->setMargin(
            marginRight: '20px',
            marginLeft:  '25px',
        );

        self::assertTrue($this->configuration->hasMargin());

        $this->configuration->setMargin();

        self::assertFalse($this->configuration->hasMargin());

        $this->configuration->setMargin(
            $marginTop    = '10px',
            $marginRight  = '20px',
            $marginBottom = '15px',
            $marginLeft   = '25px',
        );

        self::assertSame([
            'top'    => $marginTop,
            'right'  => $marginRight,
            'bottom' => $marginBottom,
            'left'   => $marginLeft,
        ], $this->configuration->getMargin());

        $this->configuration->unsetMargin();

        self::assertFalse($this->configuration->hasMargin());
    }

    public function testSerialize(): void
    {
        $expectedJson = <<<'JSON'
{
    "scale":                    1.0,
    "displayContentOnly":       false,
    "displayBackgroundGraphic": true,
    "displayTransparent":       false,
    "template":                 null,
    "pageOrientation":          "portrait",
    "pageFormat":               "A4",
    "pageRange":                "",
    "pageOverride":             true,
    "margin":                   null
}
JSON;

        self::assertJsonStringEqualsJsonString($expectedJson, \json_encode($this->configuration, \JSON_PRETTY_PRINT | \JSON_PRESERVE_ZERO_FRACTION));
    }
}
