<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TConnection;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TConnection */
final class TConnectionTest extends TestCase
{
    public function testOpenSqliteMemory(): void
    {
        $conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('sqlite', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
        $conn->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, v TEXT)');
        $conn->exec("INSERT INTO t (v) VALUES ('x')");
        $this->assertSame('x', $conn->query('SELECT v FROM t')->fetchColumn());
    }

    public function testOpenFileWithTempConfig(): void
    {
        $name = 'test_conn_' . uniqid();
        $file = "config/{$name}.php";
        file_put_contents($file, "<?php return ['type' => 'sqlite', 'name' => ':memory:'];");
        try {
            $conn = TConnection::openFile($name);
            $this->assertInstanceOf(PDO::class, $conn);
        } finally {
            unlink($file);
        }
    }

    public function testOpenFileMissingThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TConnection::openFile('no_such_config_' . uniqid());
    }

    public function testInvalidDriverThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TConnection::open(['type' => 'nosuchdriver', 'name' => 'x']);
    }
}
