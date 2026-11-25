<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /** The only database tests may ever touch. */
    public const TEST_CONNECTION = 'mysql';

    public const TEST_DATABASE = 'trade_test';

    /**
     * Tests use RefreshDatabase (migrate:fresh). If config is cached, phpunit.xml's
     * overrides are ignored and tests would wipe whatever .env points at (e.g. trade_prod).
     * So refuse to boot unless the connection is exactly mysql:trade_test. This runs
     * before any test touches the database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        static::assertSafeTestDatabase($connection, $database);

        return $app;
    }

    public static function assertSafeTestDatabase(?string $connection, ?string $database): void
    {
        if ($connection !== self::TEST_CONNECTION || $database !== self::TEST_DATABASE) {
            throw new RuntimeException(
                "Refusing to run tests against [{$connection}:{$database}]; only [".self::TEST_CONNECTION.':'.self::TEST_DATABASE.'] is allowed. Run `php artisan config:clear` first.'
            );
        }
    }
}
