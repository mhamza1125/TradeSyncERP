<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tests use RefreshDatabase (migrate:fresh). If config is cached on a server,
     * phpunit.xml's sqlite override is ignored and tests would wipe the real
     * database — so refuse to run against anything but in-memory sqlite.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Refusing to run tests against [{$connection}:{$database}]. Run `php artisan config:clear` first."
            );
        }

        return $app;
    }
}
