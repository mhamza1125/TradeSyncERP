<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\TestCase as AppTestCase;

/** The guard in tests/TestCase.php must allow exactly mysql:trade_test and nothing else. */
class TestDatabaseGuardTest extends TestCase
{
    public function test_allows_only_mysql_trade_test(): void
    {
        AppTestCase::assertSafeTestDatabase('mysql', 'trade_test');
        $this->addToAssertionCount(1);
    }

    public static function unsafeTargets(): array
    {
        return [
            'production copy' => ['mysql', 'trade_prod'],
            'scratch' => ['mysql', 'trade_scratch'],
            'legacy dev db' => ['mysql', 'tradesyncerp'],
            'case variant' => ['mysql', 'Trade_Test'],
            'suffix variant' => ['mysql', 'trade_test2'],
            'empty' => ['mysql', ''],
            'null' => ['mysql', null],
            'sqlite memory' => ['sqlite', ':memory:'],
            'other driver' => ['mariadb', 'trade_test'],
        ];
    }

    #[DataProvider('unsafeTargets')]
    public function test_refuses_everything_else(?string $connection, ?string $database): void
    {
        $this->expectException(RuntimeException::class);
        AppTestCase::assertSafeTestDatabase($connection, $database);
    }
}
