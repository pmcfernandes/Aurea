<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TBreadcrumb;
use Aurea\Core\TAction;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TBreadcrumb */
final class TBreadcrumbTest extends TestCase
{
    public function testRendersTrailWithLinks(): void
    {
        $crumb = new TBreadcrumb([
            ['label' => 'Início', 'action' => new TAction(['HomeView', 'onShow'])],
            ['label' => 'Clientes', 'action' => new TAction(['ClienteList', 'onReload'])],
            ['label' => 'Detalhe'],
        ]);
        $html = $crumb->getContents();
        $this->assertStringContainsString('aria-label="breadcrumb"', $html);
        $this->assertStringContainsString('breadcrumb-item', $html);
        $this->assertStringContainsString('Início', $html);
        $this->assertStringContainsString('Clientes', $html);
        $this->assertStringContainsString('Detalhe', $html);
        $this->assertStringContainsString('breadcrumb-item active', $html);
        $this->assertStringContainsString('index.php?class=HomeView', $html);
    }

    public function testItemWithoutActionRendersPlainText(): void
    {
        $crumb = new TBreadcrumb([
            ['label' => 'A'],
            ['label' => 'B'],
        ]);
        $html = $crumb->getContents();
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringContainsString('A', $html);
        $this->assertStringContainsString('B', $html);
    }

    public function testEscapesLabels(): void
    {
        $crumb = new TBreadcrumb([['label' => '<b>X</b>']]);
        $html = $crumb->getContents();
        $this->assertStringContainsString('&lt;b&gt;X&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>X</b>', $html);
    }
}
