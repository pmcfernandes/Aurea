<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Form\TSeekButton;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSeekButton */
final class TSeekButtonTest extends TestCase
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

    public function testAddItemsRendersDatalist(): void
    {
        $f = new TSeekButton('busca');
        $this->assertSame($f, $f->addItems(['1' => 'Um', '2' => 'Dois']));
        $html = $f->getContents();
        $this->assertStringContainsString("<datalist id='list_busca'>", $html);
        $this->assertStringContainsString('<option value="1">Um</option>', $html);
        $this->assertStringContainsString('<option value="2">Dois</option>', $html);
        $this->assertStringContainsString('<button', $html);
    }

    public function testDefaultButtonLabel(): void
    {
        $f = new TSeekButton('busca');
        $this->assertStringContainsString('🔍', $f->getContents());
    }

    public function testSetButtonLabel(): void
    {
        $f = new TSeekButton('busca');
        $this->assertSame($f, $f->setButtonLabel('Ir'));
        $this->assertStringContainsString('>Ir</button>', $f->getContents());
    }

    public function testSetSearchActionAddsOnclick(): void
    {
        $f = new TSeekButton('busca');
        $this->assertSame($f, $f->setSearchAction(new TAction(['Cli', 'onSearch']), 'q'));
        $html = $f->getContents();
        $this->assertStringContainsString('onclick=', $html);
        $this->assertStringContainsString('index.php?class=Cli', $html);
    }

    public function testSetSearchActionDefaultParam(): void
    {
        $f = new TSeekButton('busca');
        $f->setSearchAction(new TAction(['Cli', 'onSearch']));
        $this->assertStringContainsString('q=', $f->getContents());
    }
}
