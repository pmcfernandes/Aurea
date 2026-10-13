<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\TDbLogger;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\TDbLogger */
final class TDbLoggerTest extends TestCase
{
    private string $config = '';
    private string $dbFile = '';

    protected function setUp(): void
    {
        $this->config = 'tlog_test_' . uniqid();
        $this->dbFile = sys_get_temp_dir() . '/aurea_dblog_' . uniqid() . '.sqlite';
        $dbFile = $this->dbFile;
        file_put_contents(
            "config/{$this->config}.php",
            "<?php return ['type' => 'sqlite', 'name' => " . var_export($dbFile, true) . "];"
        );
    }

    protected function tearDown(): void
    {
        $cfgFile = "config/{$this->config}.php";
        if ($this->config !== '' && file_exists($cfgFile)) {
            unlink($cfgFile);
        }
        if ($this->dbFile !== '' && file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    public function testWriteCreatesTableAndRow(): void
    {
        (new TDbLogger($this->config, 'logt'))->write('hello db');
        $pdo = new \PDO("sqlite:{$this->dbFile}");
        $rows = $pdo->query('SELECT quando, mensagem FROM logt')->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $rows);
        $this->assertSame('hello db', $rows[0]['mensagem']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $rows[0]['quando']);
    }

    public function testWriteAppendsRows(): void
    {
        $log = new TDbLogger($this->config, 'logt');
        $log->write('first');
        $log->write('second');
        $pdo = new \PDO("sqlite:{$this->dbFile}");
        $count = (int) $pdo->query('SELECT COUNT(*) FROM logt')->fetchColumn();
        $this->assertSame(2, $count);
    }
}
