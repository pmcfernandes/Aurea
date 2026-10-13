<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TSortableList;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSortableList */
final class TSortableListTest extends TestCase
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

    public function testAddItemsRendersList(): void
    {
        $f = new TSortableList('ordem');
        $this->assertSame($f, $f->addItems(['a' => 'Primeiro', 'b' => 'Segundo']));
        $html = $f->getContents();
        $this->assertStringContainsString('<li', $html);
        $this->assertStringContainsString('Primeiro', $html);
        $this->assertStringContainsString("name='ordem[]'", $html);
    }

    public function testSetValueReorders(): void
    {
        $f = new TSortableList('ordem');
        $f->addItems(['a' => 'A', 'b' => 'B', 'c' => 'C']);
        $this->assertSame($f, $f->setValue(['c', 'a']));
        $html = $f->getContents();
        $posC = strpos($html, '>C</li>');
        $posA = strpos($html, '>A</li>');
        $posB = strpos($html, '>B</li>');
        $this->assertNotFalse($posC);
        $this->assertNotFalse($posA);
        $this->assertNotFalse($posB);
        $this->assertLessThan($posA, $posC);
        $this->assertLessThan($posB, $posA);
    }

    public function testSetValueIgnoresUnknownKeys(): void
    {
        $f = new TSortableList('ordem');
        $f->addItems(['a' => 'A', 'b' => 'B']);
        $f->setValue(['zzz', 'b']);
        $html = $f->getContents();
        $this->assertStringContainsString('>B</li>', $html);
        $this->assertStringContainsString('>A</li>', $html);
        $this->assertStringNotContainsString('zzz</li>', $html);
    }

    public function testEmptyListRendersUl(): void
    {
        $f = new TSortableList('ordem');
        $this->assertStringContainsString('<ul', $f->getContents());
    }
}
