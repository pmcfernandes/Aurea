<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TMultiFileField;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TMultiFileField */
final class TMultiFileFieldTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->sessionBackup = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_SESSION = $this->sessionBackup;
    }

    public function testDefaults(): void
    {
        $f = new TMultiFileField('anexos');
        $this->assertSame([], $f->getAllowedExtensions());
        $this->assertSame(10 * 1024 * 1024, $f->getMaxSize());
        $this->assertSame('file', $f->getProperty('type'));
        $this->assertSame('multiple', $f->getProperty('multiple'));
        $this->assertSame('anexos[]', $f->getProperty('name'));
    }

    public function testSetAllowedExtensions(): void
    {
        $f = new TMultiFileField('anexos');
        $this->assertSame($f, $f->setAllowedExtensions(['PDF', 'PNG']));
        $this->assertSame(['pdf', 'png'], $f->getAllowedExtensions());
    }

    public function testSetGetMaxSize(): void
    {
        $f = new TMultiFileField('anexos');
        $this->assertSame($f, $f->setMaxSize(456));
        $this->assertSame(456, $f->getMaxSize());
    }

    public function testGetContentsRendersMultipleFileInput(): void
    {
        $f = new TMultiFileField('anexos');
        $html = $f->getContents();
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('multiple', $html);
    }
}
