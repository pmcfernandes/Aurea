<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TFileField;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TFileField */
final class TFileFieldTest extends TestCase
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
        $f = new TFileField('anexo');
        $this->assertSame([], $f->getAllowedExtensions());
        $this->assertSame(10 * 1024 * 1024, $f->getMaxSize());
        $this->assertSame('file', $f->getProperty('type'));
    }

    public function testSetAllowedExtensionsLowercases(): void
    {
        $f = new TFileField('anexo');
        $this->assertSame($f, $f->setAllowedExtensions(['PDF', 'Jpg']));
        $this->assertSame(['pdf', 'jpg'], $f->getAllowedExtensions());
    }

    public function testSetMaxSize(): void
    {
        $f = new TFileField('anexo');
        $this->assertSame($f, $f->setMaxSize(123));
        $this->assertSame(123, $f->getMaxSize());
    }

    public function testGetContentsRendersFileInput(): void
    {
        $f = new TFileField('anexo');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="anexo"', $html);
    }
}
