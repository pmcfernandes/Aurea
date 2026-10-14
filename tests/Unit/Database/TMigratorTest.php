<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TConnection;
use Aurea\Database\TMigrator;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TMigrator */
final class TMigratorTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        $this->conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
    }

    private function migrator(): TMigrator
    {
        return new TMigrator($this->conn);
    }

    public function testAddSqlAndMigrate(): void
    {
        $m = $this->migrator()->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY, v TEXT)');
        $done = $m->migrate();
        $this->assertSame(['001'], $done);
        $this->assertSame(['001'], $m->applied());
        $this->assertSame([], $m->pending());
        $this->conn->exec("INSERT INTO t1 (v) VALUES ('x')");
        $this->assertSame('x', $this->conn->query('SELECT v FROM t1')->fetchColumn());
    }

    public function testAddCallback(): void
    {
        $m = $this->migrator()->addCallback('002', function (PDO $conn): void {
            $conn->exec('CREATE TABLE t2 (id INTEGER PRIMARY KEY)');
        });
        $this->assertSame(['002'], $m->pending());
        $this->assertSame(['002'], $m->migrate());
        $this->assertSame(['002'], $m->applied());
    }

    public function testMigrateAppliesOnce(): void
    {
        $m = $this->migrator()
            ->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY)')
            ->addCallback('002', function (PDO $conn): void {
                $conn->exec('CREATE TABLE t2 (id INTEGER PRIMARY KEY)');
            });
        $this->assertSame(['001', '002'], $m->migrate());
        $this->assertSame([], $m->migrate());
        $this->assertSame(['001', '002'], $m->applied());
    }

    public function testMultiStatementSql(): void
    {
        $m = $this->migrator()->add('001', 'CREATE TABLE a (id INTEGER PRIMARY KEY); CREATE TABLE b (id INTEGER PRIMARY KEY);');
        $this->assertSame(['001'], $m->migrate());
        $this->conn->exec('INSERT INTO a (id) VALUES (1)');
        $this->conn->exec('INSERT INTO b (id) VALUES (2)');
        $this->assertSame(1, (int) $this->conn->query('SELECT COUNT(*) FROM a')->fetchColumn());
        $this->assertSame(1, (int) $this->conn->query('SELECT COUNT(*) FROM b')->fetchColumn());
    }

    public function testPendingOrder(): void
    {
        $m = $this->migrator()
            ->add('002', 'CREATE TABLE t2 (id INTEGER PRIMARY KEY)')
            ->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY)');
        $this->assertSame(['001', '002'], $m->pending());
        $this->assertSame(['001', '002'], $m->migrate());
    }

    public function testDetectsChangedAppliedMigration(): void
    {
        $this->migrator()->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY)')->migrate();
        $this->expectException(\RuntimeException::class);
        $this->migrator()->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY, name TEXT)')->migrate();
    }

    public function testPersistentLockBlocksAnotherProcess(): void
    {
        $this->conn->exec('CREATE TABLE migration_lock (name VARCHAR(255) PRIMARY KEY, token VARCHAR(64) NOT NULL, locked_at INTEGER NOT NULL)');
        $stmt = $this->conn->prepare('INSERT INTO migration_lock (name, token, locked_at) VALUES (?, ?, ?)');
        $stmt->execute(['aurea_migrator', 'other-process', time()]);
        $this->expectException(\RuntimeException::class);
        $this->migrator()->add('001', 'CREATE TABLE blocked (id INTEGER)')->migrate();
    }

    public function testExpiredPersistentLockCanBeReclaimed(): void
    {
        $this->conn->exec('CREATE TABLE migration_lock (name VARCHAR(255) PRIMARY KEY, token VARCHAR(64) NOT NULL, locked_at INTEGER NOT NULL)');
        $stmt = $this->conn->prepare('INSERT INTO migration_lock (name, token, locked_at) VALUES (?, ?, ?)');
        $stmt->execute(['aurea_migrator', 'dead-process', time() - 301]);
        $this->assertSame(['001'], $this->migrator()->add('001', 'CREATE TABLE recovered (id INTEGER)')->migrate());
        $this->assertSame(0, (int) $this->conn->query('SELECT COUNT(*) FROM migration_lock')->fetchColumn());
    }
}
