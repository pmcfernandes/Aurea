<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TMultiSearch;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TMultiSearch */
final class TMultiSearchTest extends TestCase
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

    public function testAddItemsRendersCheckboxes(): void
    {
        $f = new TMultiSearch('ms');
        $this->assertSame($f, $f->addItems(['1' => 'Um', '2' => 'Dois']));
        $html = $f->getContents();
        $this->assertStringContainsString("name='ms[]'", $html);
        $this->assertStringContainsString('Um', $html);
        $this->assertStringContainsString('Dois', $html);
        $this->assertStringContainsString("type='search'", $html);
    }

    public function testSetValueMarksChecked(): void
    {
        $f = new TMultiSearch('ms');
        $f->addItems(['1' => 'Um', '2' => 'Dois']);
        $this->assertSame($f, $f->setValue(['1']));
        $html = $f->getContents();
        $this->assertStringContainsString("value='1' checked", $html);
        $this->assertStringNotContainsString("value='2' checked", $html);
    }

    public function testSetValueAcceptsScalar(): void
    {
        $f = new TMultiSearch('ms');
        $f->addItems(['a' => 'A']);
        $f->setValue('a');
        $this->assertStringContainsString("value='a' checked", $f->getContents());
    }

    public function testEmptyItemsRendersSearchOnly(): void
    {
        $f = new TMultiSearch('ms');
        $html = $f->getContents();
        $this->assertStringContainsString("type='search'", $html);
        $this->assertStringNotContainsString('checkbox', $html);
    }
}
