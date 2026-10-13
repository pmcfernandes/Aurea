<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TTransaction;
use Aurea\Log\TLoggerInterface;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TTransaction */
final class TTransactionTest extends TestCase
{
    protected function tearDown(): void
    {
        try {
            TTransaction::rollbackAll();
        } catch (\Throwable) {
        }
        $this->resetTransactionState();
    }

    private function resetTransactionState(): void
    {
        $r = new \ReflectionClass(TTransaction::class);
        foreach (['conns' => [], 'active' => null, 'logger' => null, 'dump' => false, 'loggerStrategy' => null, 'loggerFunction' => null] as $prop => $val) {
            $p = $r->getProperty($prop);
            $p->setValue(null, $val);
        }
    }

    private function memName(): string
    {
        return 'db_conn_' . uniqid();
    }

    public function testOpenConfigActivates(): void
    {
        $name = $this->memName();
        $conn = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame($conn, TTransaction::get());
        $this->assertSame($name, TTransaction::getDatabase());
        $this->assertTrue($conn->inTransaction());
    }

    public function testOpenConfigReusesConnection(): void
    {
        $name = $this->memName();
        $a = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $b = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $this->assertSame($a, $b);
        $this->assertSame($name, TTransaction::getDatabase());
    }

    public function testOpenReadsConfigFile(): void
    {
        $name = 'tx_cfg_' . uniqid();
        file_put_contents("config/{$name}.php", "<?php return ['type' => 'sqlite', 'name' => ':memory:'];");
        try {
            $conn = TTransaction::open($name);
            $this->assertInstanceOf(PDO::class, $conn);
            $this->assertSame($name, TTransaction::getDatabase());
        } finally {
            unlink("config/{$name}.php");
        }
    }

    public function testOpenMissingConfigThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TTransaction::open('no_such_' . uniqid());
    }

    public function testGetWithoutOpenThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TTransaction::get();
    }

    public function testCloseCommits(): void
    {
        $conn = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->memName());
        $conn->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, v TEXT)');
        TTransaction::close();
        $this->assertFalse($conn->inTransaction());
    }

    public function testRollbackDiscards(): void
    {
        $name = $this->memName();
        $conn = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $conn->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, v TEXT)');
        TTransaction::close();
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $conn->exec("INSERT INTO t (v) VALUES ('x')");
        TTransaction::rollback();
        $this->assertSame(0, (int) $conn->query('SELECT COUNT(*) FROM t')->fetchColumn());
    }

    public function testCloseAllAndRollbackAll(): void
    {
        $a = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->memName());
        $b = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->memName());
        $this->assertTrue($a->inTransaction());
        $this->assertTrue($b->inTransaction());
        TTransaction::closeAll();
        $this->assertFalse($a->inTransaction());
        $this->assertFalse($b->inTransaction());
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->memName());
        TTransaction::rollbackAll();
        $this->assertFalse(TTransaction::get()->inTransaction());
    }

    public function testSetLoggerFunctionAndLog(): void
    {
        $seen = [];
        TTransaction::setLoggerFunction(function (string $line) use (&$seen): void {
            $seen[] = $line;
        });
        TTransaction::log('SELECT 1', ['a' => 1]);
        $this->assertCount(1, $seen);
        $this->assertStringContainsString('SELECT 1', $seen[0]);
    }

    public function testSetLoggerStrategyAndLog(): void
    {
        $logger = new class implements TLoggerInterface {
            public array $lines = [];
            public function write(string $message): void
            {
                $this->lines[] = $message;
            }
        };
        TTransaction::setLogger($logger);
        TTransaction::log('SELECT 2');
        $this->assertSame(['SELECT 2'], $logger->lines);
    }

    public function testDumpToFileAndEcho(): void
    {
        $file = sys_get_temp_dir() . '/tx_dump_' . uniqid() . '.log';
        TTransaction::dump($file);
        TTransaction::log('SELECT 3', [7]);
        $this->assertStringContainsString('SELECT 3', (string) file_get_contents($file));
        unlink($file);
        $this->resetTransactionState();
        TTransaction::dump();
        ob_start();
        TTransaction::log('SELECT 4');
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('SELECT 4', $out);
    }
}
