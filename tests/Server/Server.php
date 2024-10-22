<?php

declare(strict_types=1);

namespace Machinateur\ThePrinter\Tests\Server;

use Machinateur\ThePrinter\Tests\Stream\ConnectionTest;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\String\UnicodeString;

if ('cli-server' === \PHP_SAPI && !\defined('__TEST_SUITE')) {
    require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';
}

/**
 * A php built-in webserver process (`http://localhost:3000`). It will use this same file as routing script,
 *  so the code below the class definition will call {@see Server::create()}.
 */
class Server extends PhpProcess implements ServerRouteProviderInterface
{
    /**
     * Fallback, when no tests are provided via `$env`.
     *
     * @deprecated will be removed
     *
     * @var array<class-string>
     */
    public const TESTS = [
        ConnectionTest::class,
    ];

    /**
     * @param array<class-string>|null $tests
     */
    public function __construct(
        public readonly string $host = 'localhost',
        public readonly int    $port = 3000,
        ?array                 $env = [],
        int                    $timeout = 0,
        ?array                 $tests = null,
    ) {
        $php = (new PhpExecutableFinder())->find(false);

        if (null !== $tests) {
            $tests = \iterator_to_array(static::loopTestCaseNames($tests));

            if ($tests) {
                $env += ['tests' => \implode(',', $tests)];
            }
        }

        parent::__construct(__FILE__, __DIR__, $env, $timeout, [$php, '-S', \sprintf('%s:%d', $host, $port), '-t', __DIR__, __FILE__]);
    }

    public function start(callable $callback = null, array $env = [], bool $waitUntilReady = true): void
    {
        parent::start($callback, $env);

        if (!$waitUntilReady) {
            return;
        }

        $this->waitUntil(static function (string $type, string $data): bool {
            return static::ERR === $type
                && 1 === \preg_match('/started$/m', $data);
        });
    }

    /**
     * Filter the given `$tests` array of {@see ServerRouteProviderInterface} class names using a generator function.
     *
     * A valid test...
     * - ... is an existing test class, for example {@see ServerTestCase}.
     * - ... implements the {@see ServerRouteProviderInterface} interface.
     *
     * Returns the valid tests' reflections class instances.
     *
     * @param array<class-string> $tests
     * @return \Generator<\ReflectionClass<ServerRouteProviderInterface>>
     */
    protected static function loopTestCases(array $tests): \Generator
    {
        foreach ($tests as $test) {
            try {
                /** @var \ReflectionClass<ServerRouteProviderInterface> $reflectionClass */
                $reflectionClass = new \ReflectionClass($test);
            } catch (\ReflectionException $e) {
                continue;
            }

            if (!$reflectionClass->implementsInterface(ServerRouteProviderInterface::class)) {
                continue;
            }

            yield $reflectionClass;
        }
    }

    /**
     * @param array<class-string> $tests
     * @return \Generator<class-string<ServerRouteProviderInterface>>
     */
    protected static function loopTestCaseNames(array $tests): \Generator
    {
        foreach (static::loopTestCases($tests) as $testClass) {
            yield $testClass->getName();
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public static function registerRoutes(RouteCollectorProxyInterface $group): void
    {
        /** @var array<class-string> $tests */
        $tests = \explode(',', \getenv('tests') ?: '');

        if (!$tests) {
            $tests = static::TESTS;
        }

        foreach (static::loopTestCases($tests) as $testClass) {
            $name = static::getRoutePathSegment($testClass->getShortName(), true);

            $group->group($name, ($testClass->getName())::registerRoutes(...));
        }
    }

    /**
     * Create a test server with the routes from all the available/desired tests.
     */
    public static function create(): App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        $app->group('/tests', static::registerRoutes(...));

        return $app;
    }

    /**
     * Build a route name segment, i.e. remove `Test` suffix, convert to snake-case (with hyphen instead of underscore)
     *  and prepend a slash (`/`) if desired.
     */
    public static function getRoutePathSegment(string $name, bool $prependSlash = false): string
    {
        $string = new UnicodeString($name);

        // Remove "Test" suffix.
        if ($string->endsWith($suffix = 'Test')) {
            $string = $string->slice(length: -1 * \strlen($suffix));
        }

        // Snake case and hyphenated.
        $string = $string->snake()
            ->replace('_', '-');

        // Prepend slash, if desired.
        if ($prependSlash) {
            $string = $string->prepend('/');
        }

        return $string->toString();
    }
}

// When run as entrypoint (router) script for the php built-in webserver, initialize the server.
if ('cli-server' === \PHP_SAPI && !\defined('__TEST_SUITE')) {
    Server::create()
        ->run();
}
