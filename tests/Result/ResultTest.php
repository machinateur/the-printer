<?php

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
