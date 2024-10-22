<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Configuration;

use Machinateur\ThePrinter\Contract\JsonObject;

/**
 * Configration mapping for `the-printer` screenshot options.
 *
 * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotoptions.md
 */
class ImageConfiguration extends JsonObject
{
    /**
     * The `png` image type.
     */
    public const TYPE_PNG = 'png';

    /**
     * The `jpg` image type.
     *
     * Alias for {@see self::TYPE_JPEG}.
     */
    public const TYPE_JPG = 'jpg';

    /**
     * The `jpeg` image type.
     */
    public const TYPE_JPEG = 'jpeg';

    /**
     * The `webp` image type.
     */
    public const TYPE_WEBP = 'webp';

    /**
     * List of allowed image types.
     *
     * @var array<string>
     */
    public const TYPE_LIST = [
        self::TYPE_PNG,
        self::TYPE_JPG,
        self::TYPE_JPEG,
        self::TYPE_WEBP,
    ];

    /**
     * The minimum quality value for a non-`png` image.
     */
    public const QUALITY_MIN = 0;

    /**
     * The maximum quality value for a non-`png` image.
     */
    public const QUALITY_MAX = 100;

    /**
     * The desired image type.
     *
     * Default is `png` when set to `null`.
     *
     * @see self::TYPE_LIST
     *
     * @var string|null
     */
    protected ?string $type = self::TYPE_PNG;

    /**
     * Quality of the image, between `0-100`. Not applicable to `png` images.
     *
     * @var int|null
     */
    protected ?int $quality = null;

    /**
     * Set the area scale.
     *
     * Has no effect with no `$area` defined. Default is `1` when set to null.
     *
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotclip.md
     *
     * @var int|null
     */
    protected ?int $scale = null;

    /**
     *
     * @see self::$scale
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotclip.md
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.boundingbox.md
     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.point.md
     *
     * @var array<string, int|null>|null
     */
    protected ?array $area = null;

    /**
     * Optimize for speed.
     *
     * @var bool
     */
    protected bool $optimize = false;

    /**
     * Limit visible content to the viewport size.
     *
     * Set to `false` (default) to capture the screenshot beyond the viewport.
     *  The default is `true` when {@see self::$area `$area`} is set.
     *
     * @var bool
     */
    protected bool $captureViewportOnly = false;

    /**
     * Capture the screenshot from the surface, rather than the view.
     *
     * @var bool
     */
    protected bool $captureSurface = true;

    /**
     * When `true`, takes a screenshot of the full page.
     *
     * @var bool
     */
    protected bool $capturePage = false;

    /**
     * Hides default white background and allows capturing screenshots with transparency.
     *
     * @var bool
     */
    protected bool $displayTransparent = false;

    /**
     * The file path to save the image to.
     *
     * The screenshot type will be inferred from file extension.
     *  If path is a relative path, then it is resolved relative to current working directory.
     *  If no path is provided, the image won't be saved to the disk.
     *
     * @deprecated not supported
     * @todo
     *
     * @var string|null
     */
    protected ?string $path = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        if (null !== $type) {
            $type = \strtolower($type);

            if (!\in_array($type, self::TYPE_LIST, true)) {
                return;
            }
        }

        $this->type = $type;
    }

    public function hasType(): bool
    {
        return null !== $this->type;
    }

    public function isType(string $type): bool
    {
        return $this->hasType()
            && $this->type === \strtolower($type);
    }

    public function unsetType(): void
    {
        $this->setType(null);
    }

    public function resetType(): void
    {
        $this->setType(self::TYPE_PNG);
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(?int $quality): void
    {
        if (null !== $quality) {
            $quality = \abs($quality);

            $quality = \min(\max($quality, self::QUALITY_MIN), self::QUALITY_MAX);
        }

        $this->quality = $quality;
    }

    public function hasQuality(): bool
    {
        return null !== $this->quality;
    }

    public function unsetQuality(): void
    {
        $this->setQuality(null);
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(?int $scale): void
    {
        if (null !== $scale) {
            $scale = \abs($scale);
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

    /**
     * @return array<string, int|null>|null
     */
    public function getArea(): ?array
    {
        return $this->area;
    }

    public function setArea(
        int|null $areaX,
        int|null $areaY,
        int|null $areaWidth,
        int|null $areaHeight,
    ): void
    {
        $this->area = [
            'x'      => $areaX,
            'y'      => $areaY,
            'width'  => $areaWidth,
            'height' => $areaHeight,
        ];
    }

    public function hasArea(): bool
    {
        return null !== $this->area;
    }

    public function unsetArea(): void
    {
        $this->area = null;
    }

    public function isOptimize(): bool
    {
        return $this->optimize;
    }

    public function setOptimize(bool $optimize): void
    {
        $this->optimize = $optimize;
    }

    public function isCaptureViewportOnly(): bool
    {
        return $this->captureViewportOnly;
    }

    public function setCaptureViewportOnly(bool $captureViewportOnly): void
    {
        $this->captureViewportOnly = $captureViewportOnly;
    }

    public function isCaptureSurface(): bool
    {
        return $this->captureSurface;
    }

    public function setCaptureSurface(bool $captureSurface): void
    {
        $this->captureSurface = $captureSurface;
    }

    public function isCapturePage(): bool
    {
        return $this->capturePage;
    }

    public function setCapturePage(bool $capturePage): void
    {
        $this->capturePage = $capturePage;
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
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        // Remove options that are not supported.
        unset($serialized['path']);

        return $serialized;
    }
}
