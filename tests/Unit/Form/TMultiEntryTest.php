<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TMultiEntry;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TMultiEntry */
final class TMultiEntryTest extends TestCase
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

    public function testSetValueRendersChips(): void
    {
        $f = new TMultiEntry('tags');
        $this->assertSame($f, $f->setValue(['php', 'aurea']));
        $html = $f->getContents();
        $this->assertStringContainsString('me-chip', $html);
        $this->assertStringContainsString("name='tags[]' value='php'", $html);
        $this->assertStringContainsString("name='tags[]' value='aurea'", $html);
    }

    public function testSetPlaceholder(): void
    {
        $f = new TMultiEntry('tags');
        $this->assertSame($f, $f->setPlaceholder('Escreva e prima Enter'));
        $html = $f->getContents();
        $this->assertStringContainsString("placeholder='Escreva e prima Enter'", $html);
        $this->assertSame('Escreva e prima Enter', $f->getProperty('placeholder'));
    }

    public function testEmptyRendersInput(): void
    {
        $html = (new TMultiEntry('tags'))->getContents();
        $this->assertStringContainsString("type='text'", $html);
        $this->assertStringNotContainsString("<span class='me-chip'>", $html);
    }
}
