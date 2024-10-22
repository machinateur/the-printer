<?php
/*
 * MIT License
 *
 * Copyright (c) 2020-2024 machinateur
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Machinateur\ThePrinter\Configuration;

use Machinateur\ThePrinter\Contract\JsonObject;

/**
 * Configration mapping for `the-printer` PDF options.
 *
 * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfoptions.md
 */
class DocumentConfiguration extends JsonObject
{
    /**
     * The minimum scale for a document.
     */
    public const SCALE_MIN = 0.1;

    /**
     * The maximum scale for a document.
     */
    public const SCALE_MAX = 2.0;

    /**
     * The default scale of a document.
     */
    public const SCALE_DEFAULT = 1.0;

    /**
     * The template header key.
     */
    public const TEMPLATE_HEADER = 'header';

    /**
     * The template footer key.
     */
    public const TEMPLATE_FOOTER = 'footer';

    /**
     * The portrait orientation for a document.
     */
    public const ORIENTATION_PORTRAIT = 'portrait';

    /**
     * The landscape orientation for a document.
     */
    public const ORIENTATION_LANDSCAPE = 'landscape';

    /**
     * Letter: 8.5in x 11in
     */
    public const FORMAT_LETTER = 'Letter';

    /**
     * Legal: 8.5in x 14in
     */
    public const FORMAT_LEGAL = 'Legal';

    /**
     * Tabloid:  11in x 17in
     */
    public const FORMAT_TABLOID = 'Tabloid';

    /**
     * Ledger:  17in x 11in
     */
    public const FORMAT_LEDGER = 'Ledger';

    /**
     * A0: 33.1102in x 46.811in
     */
    public const FORMAT_A0 = 'A0';

    /**
     * A1: 23.3858in x 33.1102in
     */
    public const FORMAT_A1 = 'A1';

    /**
     * A2: 16.5354in x 23.3858in
     */
    public const FORMAT_A2 = 'A2';

    /**
     * A3: 11.6929in x 16.5354in
     */
    public const FORMAT_A3 = 'A3';

    /**
     * A4: 8.2677in x 11.6929in
     */
    public const FORMAT_A4 = 'A4';

    /**
     * A5: 5.8268in x 8.2677in
     */
    public const FORMAT_A5 = 'A5';

    /**
     * 4.1339in x 5.8268in
     */
    public const FORMAT_A6 = 'A6';

    /**
     * List of allowed formats.
     *
     * @var array<string>
     */
    public const FORMAT_LIST = [
        self::FORMAT_LETTER,
        self::FORMAT_LEGAL,
        self::FORMAT_TABLOID,
        self::FORMAT_LEDGER,
        self::FORMAT_A0,
        self::FORMAT_A1,
        self::FORMAT_A2,
        self::FORMAT_A3,
        self::FORMAT_A4,
        self::FORMAT_A5,
        self::FORMAT_A6,
    ];

    /**
     * Scales the rendering of the web page. Amount must be between `0.1` and `2.0`.
     *
     * @var float|null
     */
    protected ?float $scale = 1.0;

    /**
     * Whether to show the header and footer.
     *
     * @var bool
     */
    protected bool $displayContentOnly = false;

    /**
     * Set to `true` to print background graphics.
     *
     * @var bool
     */
    protected bool $displayBackgroundGraphic = true;

    /**
     * Hides default white background and allows generating pdfs with transparency.
     *
     * @var bool
     */
    protected bool $displayTransparent = false;

    /**
     * HTML template for the print header.
     *  Should be valid HTML with the following classes used to inject values into them:
     *
     * - `date`       = formatted print date
     * - `title`      = document title
     * - `url`        = document location
     * - `pageNumber` = current page number
     * - `totalPages` = total pages in the document
     *
     * @var array<string, string>|null
     */
    protected ?array $template = null;

    /**
     * Decides whether to print in landscape orientation.
     *
     * @see self::ORIENTATION_PORTRAIT
     * @see self::ORIENTATION_LANDSCAPE
     *
     * @var string
     */
    protected string $pageOrientation = self::ORIENTATION_PORTRAIT;

    /**
     * **Remarks**:
     *
     * If set, this takes priority over the width and height options.
     *
     * @see self::FORMAT_LIST
     *
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.paperformat.md
     *
     * @var string|null
     */
    protected ?string $pageFormat = self::FORMAT_A4;

    /**
     * Sets the height of paper. You can pass in a number or a string with a unit.
     *
     * @var string|int|null
     */
    protected string|int|null $pageWidth = null;

    /**
     * Sets the width of paper. You can pass in a number or a string with a unit.
     *
     * @var string|int|null
     */
    protected string|int|null $pageHeight = null;

    /**
     * Paper ranges to print, e.g. 1-5, 8, 11-13.
     *  Set to an empty string, means all pages are printed.
     *
     * @var string
     */
    protected string $pageRange = '';

    /**
     * Give any CSS `page` size declared in the page priority
     *  over what is declared in the `width` or `height` or `format` option.
     *
     * Set to `false` to scale the content to fit the given paper size (`format` or `width`/`height`).
     *
     * @var bool
     */
    protected bool $pageOverride = true;

    /**
     * Set the PDF margins.
     *
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfmargin.md
     *
     * @var array<string, string|int>|null
     */
    protected ?array $margin = null;

    /**
     * The path to save the file to.
     *  Set to `null` which means the PDF will not be written to disk.
     *
     * **Remarks**:
     *
     * If the path is relative, it's resolved relative to the current working directory.
     *
     * @deprecated not supported
     * @todo
     *
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * Timeout in milliseconds. Pass 0 to disable timeout.
     *
     * Timeout is disabled by default.
     *
     * @deprecated not supported
     * @todo
     *
     * @var int
     */
    protected int $timeout = 0;

    /**
     * @deprecated not supported
     * @todo
     *
     * @var bool
     */
    protected bool $waitForFonts = true;

    public function getScale(): ?float
    {
        return $this->scale;
    }

    public function setScale(?float $scale): void
    {
        if (null !== $scale) {
            $scale = \abs($scale);

            $scale = \min(\max($scale, self::SCALE_MIN), self::SCALE_MAX);
        }

        $this->scale = $scale;
    }

    public function hasScale(): bool
    {
        return null !== $this->scale;
    }

    public function unsetScale(): void
    {
        $this->setScale(null);
    }

    public function resetScale(): void
    {
        $this->setScale(self::SCALE_DEFAULT);
    }

    public function isDisplayContentOnly(): bool
    {
        return $this->displayContentOnly;
    }

    public function setDisplayContentOnly(bool $displayContentOnly): void
    {
        $this->displayContentOnly = $displayContentOnly;
    }

    public function isDisplayBackgroundGraphic(): bool
    {
        return $this->displayBackgroundGraphic;
    }

    public function setDisplayBackgroundGraphic(bool $displayBackgroundGraphic): void
    {
        $this->displayBackgroundGraphic = $displayBackgroundGraphic;
    }

    public function isDisplayTransparent(): bool
    {
        return $this->displayTransparent;
    }

    public function setDisplayTransparent(bool $displayTransparent): void
    {
        $this->displayTransparent = $displayTransparent;
    }

    /**
     * @return array<string, string>|null
     */
    public function getTemplate(): ?array
    {
        return $this->template;
    }

    /**
     * @param array<string, string|null>|null $template
     * @return void
     */
    public function setTemplate(?array $template): void
    {
        if (null !== $template) {
            $template = \array_intersect_key($template,
                \array_flip([
                    self::TEMPLATE_HEADER,
                    self::TEMPLATE_FOOTER,
                ])
            );
            /** @var array<string, string> $template */
            $template = \array_filter($template, \is_string(...));

            if (empty($template)) {
                $template = null;
            }
        }

        $this->template = $template;
    }

    public function hasTemplate(): bool
    {
        return null !== $this->template;
    }

    public function unsetTemplate(): void
    {
        $this->setTemplate(null);
    }

    public function getTemplateHeader(): ?string
    {
        return $this->template[self::TEMPLATE_HEADER] ?? null;
    }

    public function setTemplateHeader(?string $templateHeader): void
    {
        $template = $this->getTemplate() ?? [];
        $template[self::TEMPLATE_HEADER] = $templateHeader;
        $this->setTemplate($template);
    }

    public function hasTemplateHeader(): bool
    {
        return isset($this->template[self::TEMPLATE_HEADER]);
    }

    public function unsetTemplateHeader(): void
    {
        $this->setTemplateHeader(null);
    }

    public function getTemplateFooter(): ?string
    {
        return $this->template[self::TEMPLATE_FOOTER] ?? null;
    }

    public function setTemplateFooter(?string $templateFooter): void
    {
        $template = $this->getTemplate() ?? [];
        $template[self::TEMPLATE_FOOTER] = $templateFooter;
        $this->setTemplate($template);
    }

    public function hasTemplateFooter(): bool
    {
        return isset($this->template[self::TEMPLATE_FOOTER]);
    }

    public function unsetTemplateFooter(): void
    {
        $this->setTemplateFooter(null);
    }

    public function getPageOrientation(): string
    {
        return $this->pageOrientation;
    }

    public function setPageOrientation(string $pageOrientation): void
    {
        if (!\in_array($pageOrientation, [self::ORIENTATION_PORTRAIT, self::ORIENTATION_LANDSCAPE], true)) {
            return;
        }

        $this->pageOrientation = $pageOrientation;
    }

    public function isPageOrientation(string $pageOrientation): bool
    {
        return $this->pageOrientation === $pageOrientation;
    }

    public function resetPageOrientation(): void
    {
        $this->setPageOrientation(self::ORIENTATION_PORTRAIT);
    }

    public function getPageFormat(): ?string
    {
        return $this->pageFormat;
    }

    public function setPageFormat(?string $pageFormat): void
    {
        if (null !== $pageFormat) {
            $pageFormat = \strtoupper($pageFormat);

            if (!\in_array($pageFormat, \array_map(\strtoupper(...), self::FORMAT_LIST), true)) {
                return;
            }
        }

        $this->pageFormat = $pageFormat;
    }

    public function hasPageFormat(): bool
    {
        return null !== $this->pageFormat;
    }

    public function isPageFormat(string $pageFormat): bool
    {
        return $this->hasPageFormat()
            && $this->pageFormat === \strtoupper($pageFormat);
    }

    public function unsetPageFormat(): void
    {
        $this->setPageFormat(null);
    }

    public function resetPageFormat(): void
    {
        $this->setPageFormat(self::FORMAT_A4);
    }

    public function getPageWidth(): int|string|null
    {
        return $this->pageWidth;
    }

    public function setPageWidth(int|string|null $pageWidth): void
    {
        if (\is_numeric($pageWidth)) {
            $pageWidth = (int)$pageWidth;
        }

        if (\is_int($pageWidth)) {
            $pageWidth = \abs($pageWidth);
        }

        $this->pageWidth = $pageWidth;
    }

    public function hasPageWidth(): bool
    {
        return null !== $this->pageWidth;
    }

    public function unsetPageWidth(): void
    {
        $this->setPageWidth(null);
    }

    public function getPageHeight(): int|string|null
    {
        return $this->pageHeight;
    }

    public function setPageHeight(int|string|null $pageHeight): void
    {
        if (\is_numeric($pageHeight)) {
            $pageHeight = (int)$pageHeight;
        }

        if (\is_int($pageHeight)) {
            $pageHeight = \abs($pageHeight);
        }

        $this->pageHeight = $pageHeight;
    }

    public function hasPageHeight(): bool
    {
        return null !== $this->pageHeight;
    }

    public function unsetPageHeight(): void
    {
        $this->setPageHeight(null);
    }

    public function getPageRange(): string
    {
        return $this->pageRange;
    }

    public function setPageRange(string $pageRange): void
    {
        $this->pageRange = $pageRange;
    }

    public function hasPageRange(): bool
    {
        return '' !== $this->pageRange;
    }

    public function unsetPageRange(): void
    {
        $this->setPageRange('');
    }

    public function isPageOverride(): bool
    {
        return $this->pageOverride;
    }

    public function setPageOverride(bool $pageOverride): void
    {
        $this->pageOverride = $pageOverride;
    }

    /**
     * @return array<string, string|int>|null
     */
    public function getMargin(): ?array
    {
        return $this->margin;
    }

    public function setMargin(
        string|int|null $marginTop    = null,
        string|int|null $marginRight  = null,
        string|int|null $marginBottom = null,
        string|int|null $marginLeft   = null,
    ): void {
        $margin = [
            'top'    => $marginTop,
            'right'  => $marginRight,
            'bottom' => $marginBottom,
            'left'   => $marginLeft,
        ];
        $margin = \array_filter($margin);

        if (0 === \count($margin)) {
            $margin = null;
        } elseif ($this->hasMargin()) {
            \assert(null !== $this->margin);

            $margin = \array_replace($this->margin, $margin);
        }

        $this->margin = $margin;
    }

    public function hasMargin(): bool
    {
        return null !== $this->margin;
    }

    public function unsetMargin(): void
    {
        $this->margin = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        // Remove page dimension if not given for both sides of the document.
        if (!$this->hasPageWidth() || !$this->hasPageHeight()) {
            unset($serialized['pageWidth']);
            unset($serialized['pageHeight']);
        }

        // Remove options that are not supported.
        unset($serialized['path']);
        unset($serialized['timeout']);
        unset($serialized['waitForFonts']);

        return $serialized;
    }
}
