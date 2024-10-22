<?php

// Part of `machinateur/the-printer` test-suite (phpt).

declare(strict_types=1);

function require_php_version(string $phpVersion): void
{
    if (!\version_compare(\PHP_VERSION, $phpVersion, '>=')) {
        die(\sprintf('Skipped: PHP >=%s required (has %s).', $phpVersion, \PHP_VERSION));
    }
}

function require_extension(string $extension): void
{
    if (!\extension_loaded($extension)) {
        die(\sprintf('Skipped: Extension %s required.', $extension));
    }
}

// Minimum version requirement.
\require_php_version('8.1.0');
// Library requirements (see `composer.json`)
\require_extension('json');
\require_extension('curl');
// Testing requirements.
\require_extension('imagick');
