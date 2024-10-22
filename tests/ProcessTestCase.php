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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * A test case base class, where the test requires a background process running.
 *  The process is automatically managed while the test case is executed.
 */
abstract class ProcessTestCase extends TestCase
{
    protected static ?Process $process = null;

    /**
     * Create the background process instance.
     */
    abstract protected static function createProcess(): Process;

    /**
     * Initialize and start the background process, if not yet up and running.
     *
     * @beforeClass
     */
    public static function startProcess(): void
    {
        if (null === static::$process) {
            $process = static::$process = static::createProcess();
        } else {
            $process = static::$process;
        }

        if ($process->isRunning()) {
            return;
        }

        $process->start();

        if (!$process->isRunning()) {
            throw new \RuntimeException('Process is not started.');
        }
    }

    /**
     * Stop and destroy the background process, if still up and running.
     *
     * @afterClass
     */
    public static function stopProcess(): void
    {
        if (null === static::$process) {
            return;
        } else {
            $process = static::$process;
        }

        if ($process->isRunning()) {
            $process->stop();
        }

        static::$process = null;
    }

    /**
     * @after
     */
    public static function clearProcessOutput(): void
    {
        if (null === static::$process) {
            return;
        }

        static::$process->clearOutput();
        static::$process->clearErrorOutput();
    }

    /**
     * Assert the background process is currently running.
     */
    final public static function assertProcessRunning(): void
    {
        self::assertTrue(static::$process->isRunning(), 'Process is not running.');
    }

    /**
     * Assert the background process is currently running.
     */
    final public static function assertProcessNotRunning(): void
    {
        self::assertFalse(static::$process->isRunning(), 'Process is running.');
    }
}
