<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Form\TUniqueSearch;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TUniqueSearch */
final class TUniqueSearchTest extends TestCase
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
        $f = new TUniqueSearch('uni');
        $this->assertSame($f, $f->addItems(['1' => 'Um', '2' => 'Dois']));
        $html = $f->getContents();
        $this->assertStringContainsString("<datalist id='list_uni'>", $html);
        $this->assertStringContainsString('<option value="1">Um</option>', $html);
    }

    public function testSetMinLength(): void
    {
        $f = new TUniqueSearch('uni');
        $this->assertSame($f, $f->setMinLength(3));
        $this->assertSame('3', $f->getProperty('minlength'));
        $this->assertStringContainsString('minlength', $f->getContents());
    }

    public function testSetChangeActionAddsOnchange(): void
    {
        $f = new TUniqueSearch('uni');
        $this->assertSame($f, $f->setChangeAction(new TAction(['Cli', 'onChange']), 'key'));
        $html = $f->getContents();
        $this->assertStringContainsString('onchange=', $html);
        $this->assertStringContainsString('index.php?class=Cli', $html);
    }

    public function testWithoutChangeActionHasNoOnchange(): void
    {
        $f = new TUniqueSearch('uni');
        $this->assertStringNotContainsString('onchange=', $f->getContents());
    }
}
