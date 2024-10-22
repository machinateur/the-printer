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

namespace Machinateur\ThePrinter\Tests\Result;

use Machinateur\ThePrinter\Tests\EmbeddedPhptTestSuiteBehaviour;
use Machinateur\ThePrinter\Tests\ProcessTestCase;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * A test class to execute the PHPT tests, embedded in the project's test-suite.
 *
 * @coversNothing
 */
class ResultTest extends ProcessTestCase
{
    use EmbeddedPhptTestSuiteBehaviour;

    /**
     * Initialization of the background process object for managing the node server.
     */
    protected static function createProcess(): Process
    {
        $node = (new ExecutableFinder())->find('node');

        // A node executable is required to execute this test.
        self::assertNotNull($node);

        return new Process([$node, \dirname(__DIR__, 2) . '/the-printer.js'], env: null);
    }

    public function testRun(): void
    {
        self::assertProcessRunning();

        self::$process->waitUntil(static function (string $type, string $data): bool {
            return Process::OUT === $type
                && 1 === \preg_match('/^App at /m', $data);
        });

        self::runPhptTestSuite(__DIR__ . '/test_*.phpt');
    }
}
