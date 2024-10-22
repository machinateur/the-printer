<?php

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
