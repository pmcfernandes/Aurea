<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TSidebar;
use Aurea\Core\TAction;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TSidebar */
final class TSidebarTest extends TestCase
{
    public function testBrandRendering(): void
    {
        $bar = new TSidebar('Aurea');
        $html = $bar->getContents();
        $this->assertStringContainsString('sidebar-brand', $html);
        $this->assertStringContainsString('Aurea', $html);
    }

    public function testAddSection(): void
    {
        $bar = new TSidebar('Aurea');
        $this->assertSame($bar, $bar->addSection('Principal'));
        $html = $bar->getContents();
        $this->assertStringContainsString('Principal', $html);
        $this->assertStringContainsString('sidebar-section', $html);
    }

    public function testAddItem(): void
    {
        $bar = new TSidebar('Aurea');
        $action = new TAction(['HomeView', 'onShow']);
        $this->assertSame($bar, $bar->addItem('Painel', $action, 'fa-house', true));
        $html = $bar->getContents();
        $this->assertStringContainsString('Painel', $html);
        $this->assertStringContainsString('fa-house', $html);
        $this->assertStringContainsString('nav-link active', $html);
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $html);
    }

    public function testAddItemWithoutAction(): void
    {
        $bar = new TSidebar('Aurea');
        $bar->addItem('Simples');
        $html = $bar->getContents();
        $this->assertStringContainsString('Simples', $html);
        $this->assertStringContainsString("href='#'", $html);
        $this->assertStringNotContainsString('nav-link active', $html);
    }
}
