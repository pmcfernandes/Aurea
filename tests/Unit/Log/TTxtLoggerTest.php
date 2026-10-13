<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\TTxtLogger;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\TTxtLogger */
final class TTxtLoggerTest extends TestCase
{
    private string $file = '';

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/aurea_txt_' . uniqid() . '.log';
    }

    protected function tearDown(): void
    {
        if ($this->file !== '' && file_exists($this->file)) {
            unlink($this->file);
        }
    }

    public function testWriteCreatesFileWithMessage(): void
    {
        (new TTxtLogger($this->file))->write('hello txt');
        $this->assertFileExists($this->file);
        $content = (string) file_get_contents($this->file);
        $this->assertStringContainsString('hello txt', $content);
        $this->assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $content);
    }

    public function testWriteAppends(): void
    {
        $log = new TTxtLogger($this->file);
        $log->write('first');
        $log->write('second');
        $content = (string) file_get_contents($this->file);
        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
    }

    public function testWriteCreatesMissingDirectory(): void
    {
        $dir = sys_get_temp_dir() . '/aurea_txtdir_' . uniqid();
        $file = $dir . '/sub/log.txt';
        try {
            (new TTxtLogger($file))->write('nested');
            $this->assertFileExists($file);
            $this->assertStringContainsString('nested', (string) file_get_contents($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (is_dir($dir . '/sub')) {
                rmdir($dir . '/sub');
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
