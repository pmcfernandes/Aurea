<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\THtmlLogger;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\THtmlLogger */
final class THtmlLoggerTest extends TestCase
{
    private string $file = '';

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/aurea_html_' . uniqid() . '.html';
    }

    protected function tearDown(): void
    {
        if ($this->file !== '' && file_exists($this->file)) {
            unlink($this->file);
        }
    }

    public function testWriteCreatesHtmlTable(): void
    {
        (new THtmlLogger($this->file))->write('hello html');
        $this->assertFileExists($this->file);
        $content = (string) file_get_contents($this->file);
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('hello html', $content);
    }

    public function testWriteEscapesMessage(): void
    {
        (new THtmlLogger($this->file))->write('<b>x</b>');
        $content = (string) file_get_contents($this->file);
        $this->assertStringContainsString('&lt;b&gt;', $content);
        $this->assertStringNotContainsString('<b>x</b>', $content);
    }

    public function testWriteAppendsRows(): void
    {
        $log = new THtmlLogger($this->file);
        $log->write('first');
        $log->write('second');
        $content = (string) file_get_contents($this->file);
        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
        $this->assertSame(1, substr_count($content, '<table'));
    }
}
