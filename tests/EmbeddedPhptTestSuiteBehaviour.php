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

namespace Machinateur\ThePrinter\Tests;

use PHPUnit\Framework\TestSuite;

trait EmbeddedPhptTestSuiteBehaviour
{
    /**
     * Execute an embedded {@see TestSuite} for PHPT tests matching the given path (pattern).
     *
     * @param string $pattern   A glob pattern to select PHPT files.
     */
    private static function runPhptTestSuite(string $pattern, string $name = 'Embedded PHPT'): void
    {
        self::assertStringEndsWith('.phpt', $pattern);

        $iterator  = new \GlobIterator($pattern);

        // Create embedded test-suite from PHPT files in this directory.
        $testSuite = TestSuite::empty($name);
        $testSuite->addTestFiles($iterator);

        $testSuite->run();
    }
}
