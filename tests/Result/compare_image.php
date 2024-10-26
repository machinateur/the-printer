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

use Machinateur\ThePrinter\Client;
use Machinateur\ThePrinter\Configuration\ImageConfiguration;

require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';

// Prepare input arguments.
\array_shift($argv);
\assert(4 === \count($argv));

// Unpack input arguments.
[$controlFile, $compareFile, $template, $tolerance] = $argv;

// File paths are relative to the project root, but must lead to `./res`.
\assert(\is_file($controlFile) && \str_ends_with(__DIR__ . '/res', \dirname($controlFile)), 'Invalid control file');
\assert(!\is_file($compareFile) && \str_ends_with(__DIR__ . '/res', \dirname($compareFile)), 'Invalid compare file');
\assert(\is_file($template) && \str_ends_with(__DIR__ . '/res', \dirname($template)), 'Invalid template file');
// The tolerance must be an integer >0.
\assert(\is_numeric($tolerance), 'Invalid tolerance');
$tolerance = (int)$tolerance ?: 10;

// Get compare file handle.
$fh = \fopen($compareFile, 'w');

// Create a new client instance to use.
try {
    $client = new Client('http://localhost:3000/');
    $client->image(new ImageConfiguration(), $template, $fh);
} catch (\Throwable $error) {
    if (\file_exists($compareFile)) {
        // Close the file, if still open.
        if (\is_resource($fh)) {
            \fclose($fh);
        }

        // Error, so we will not reach the "CLEAN" section of the PHPT file. Remove the file to avoid failing tests.
        \unlink($compareFile);
    }

    echo $error->getMessage(), \PHP_EOL;

    throw $error;
}

// Close the file. The "CLEAN" section will take care of unlink()ing it.
\fclose($fh);

// Compare using imagick.
$diff       = \compare_with_imagick($controlFile, $compareFile, fuzziness: 5);
$diffResult = \number_format($diff, 4) . '%';

if ($diff <= $tolerance) {
    echo "matching within tolerance ({$diffResult})", \PHP_EOL;
} else {
    echo "surpassing tolerance ({$diffResult})", \PHP_EOL;
}
