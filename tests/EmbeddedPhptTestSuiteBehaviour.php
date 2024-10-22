<?php

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
