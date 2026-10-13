<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TTabs;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TTabs */
final class TTabsTest extends TestCase
{
    public function testAppendTabRendersTabs(): void
    {
        $tabs = new TTabs;
        $this->assertSame($tabs, $tabs->appendTab('Aba 1', 'conteúdo 1'));
        $tabs->appendTab('Aba 2', 'conteúdo 2');
        $html = $tabs->getContents();
        $this->assertStringContainsString('nav-tabs', $html);
        $this->assertStringContainsString('Aba 1', $html);
        $this->assertStringContainsString('Aba 2', $html);
        $this->assertStringContainsString('conteúdo 1', $html);
        $this->assertStringContainsString('conteúdo 2', $html);
        $this->assertStringContainsString('tab-pane', $html);
    }

    public function testFirstTabIsActive(): void
    {
        $tabs = new TTabs;
        $tabs->appendTab('Um', 'x')->appendTab('Dois', 'y');
        $html = $tabs->getContents();
        $this->assertStringContainsString('nav-link active', $html);
        $this->assertStringContainsString('show active', $html);
    }

    public function testEscapesTitleAndRendersComponent(): void
    {
        $tabs = new TTabs;
        $inner = new TComponent('b');
        $inner->add('negrito');
        $tabs->appendTab('<Tag>', $inner);
        $html = $tabs->getContents();
        $this->assertStringContainsString('&lt;Tag&gt;', $html);
        $this->assertStringNotContainsString('<Tag>', $html);
        $this->assertStringContainsString('<b>negrito</b>', $html);
    }
}
