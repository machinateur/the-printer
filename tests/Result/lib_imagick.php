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

// Part of `machinateur/the-printer` test-suite (phpt).

declare(strict_types=1);

/**
 * Compare the given control file to the compare file.
 *
 * Return the difference, according to the {@see \Imagick::METRIC_ABSOLUTEERRORMETRIC absolute metric}, in percent.
 *
 * Optionally, write the reconstruction image to the provided stream (in `png` format).
 *
 * Recommended options:
 *
 * ```
 * fuzziness: 5,
 * ```
 *
 * In case of security policy issues, comment out the following line of the `/etc/ImageMagick-6/policy.xml` configuration:
 *
 * ```
 * <policy domain="coder" rights="none" pattern="PDF" />
 * ```
 *
 * @see https://karlomikus.com/blog/compare-pdf-files-using-php-and-imagemagick
 * @see https://imagemagick.org/script/command-line-options.php#fuzz
 *
 * @param string        $controlFile    The path for the control file.
 * @param string        $compareFile    The path of the compare file.
 * @param array{
 *     0: int,
 *     1: int,
 * }                    $resolution     The override resolution (applied to both images).
 * @param int           $fuzziness      The fuzziness setting to use (unsigned int) in percent.
 * @param resource|null $fh             A stream resource to write the reconstructed file to.
 *
 * @return float                        The diff in percentage.
 *
 * @throws ImagickException in case of an error during the operation
 */
function compare_with_imagick(
    string       $controlFile,
    string       $compareFile,
    array        $resolution = [],
    int          $fuzziness  = 0,
    /*resource*/ $fh         = null,
): float
{
    // Create instance.
    $controlInstance = new \Imagick();
    $compareInstance = new \Imagick();

    // Adapt resolution.
    if ($resolution) {
        $controlInstance->setResolution(...$resolution);
        $compareInstance->setResolution(...$resolution);
    }

    // Set tolerance.
    if ($fuzziness) {
        $controlInstance->setOption('fuzz', \sprintf('%u%%', $fuzziness));
    }

    // Load image.
    $controlInstance->readImage($controlFile);
    $compareInstance->readImage($compareFile);

    /**
     * @var array{
     *     0: \Imagick,
     *     1: float,
     * } $result
     */
    $result = $controlInstance->compareImages($compareInstance, \Imagick::METRIC_ABSOLUTEERRORMETRIC);

    // Save result, if needed.
    if (\is_resource($fh)) {
        $result[0]->setImageFormat('png');
        $result[0]->writeImageFile($fh);
    }

    // Return
    return ($result[1] * 100) / ($controlInstance->getImageWidth() * $controlInstance->getImageHeight());
}
