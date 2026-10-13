<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\TXmlLogger;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\TXmlLogger */
final class TXmlLoggerTest extends TestCase
{
    private string $file = '';

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/aurea_xml_' . uniqid() . '.xml';
    }

    protected function tearDown(): void
    {
        if ($this->file !== '' && file_exists($this->file)) {
            unlink($this->file);
        }
    }

    public function testWriteCreatesValidXml(): void
    {
        (new TXmlLogger($this->file))->write('hello xml');
        $this->assertFileExists($this->file);
        $xml = simplexml_load_file($this->file);
        $this->assertNotFalse($xml);
        $this->assertSame('hello xml', (string) $xml->entry[0]->message);
        $this->assertNotEmpty((string) $xml->entry[0]['time']);
    }

    public function testWriteAppendsEntries(): void
    {
        $log = new TXmlLogger($this->file);
        $log->write('first');
        $log->write('second');
        $xml = simplexml_load_file($this->file);
        $this->assertNotFalse($xml);
        $this->assertCount(2, $xml->entry);
        $this->assertSame('first', (string) $xml->entry[0]->message);
        $this->assertSame('second', (string) $xml->entry[1]->message);
    }

    public function testWriteEscapesSpecialChars(): void
    {
        (new TXmlLogger($this->file))->write('a < b & c');
        $xml = simplexml_load_file($this->file);
        $this->assertNotFalse($xml);
        $this->assertSame('a < b & c', (string) $xml->entry[0]->message);
    }
}
