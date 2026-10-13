<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Form\TSearchField;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSearchField */
final class TSearchFieldTest extends TestCase
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

    public function testGetContentsRendersHiddenAndVisible(): void
    {
        $html = (new TSearchField('cli'))->getContents();
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="cli"', $html);
        $this->assertStringContainsString('name="cli_label"', $html);
        $this->assertStringContainsString('minlength="2"', $html);
    }

    public function testSetValue(): void
    {
        $f = new TSearchField('cli');
        $this->assertSame($f, $f->setValue(5, 'Ana'));
        $html = $f->getContents();
        $this->assertStringContainsString('value="5"', $html);
        $this->assertStringContainsString('value="Ana"', $html);
    }

    public function testSettersAreFluent(): void
    {
        $f = new TSearchField('cli');
        $this->assertSame($f, $f->setSearchAction(new TAction(['Cli', 'onSearch'])));
        $this->assertSame($f, $f->setIdKey('id'));
        $this->assertSame($f, $f->setLabelKey('nome'));
        $this->assertSame($f, $f->setMinLength(3));
        $html = $f->getContents();
        $this->assertStringContainsString('minlength="3"', $html);
        $this->assertStringContainsString('index.php?class=Cli', $html);
    }

    public function testCustomKeysAppearInJs(): void
    {
        $f = new TSearchField('cli');
        $f->setIdKey('codigo')->setLabelKey('descricao');
        $html = $f->getContents();
        $this->assertStringContainsString('"codigo"', $html);
        $this->assertStringContainsString('"descricao"', $html);
    }
}
