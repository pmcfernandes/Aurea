<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Core\TAction;
use Aurea\Widget\TTreeView;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TTreeView */
final class TTreeViewTest extends TestCase
{
    public function testAddItemHierarchy(): void
    {
        $tree = new TTreeView;
        $this->assertSame($tree, $tree->addItem('a', null, 'Raiz'));
        $tree->addItem('b', 'a', 'Filho');
        $tree->addItem('c', 'b', 'Neto');
        $html = $tree->getContents();
        $this->assertStringContainsString('Raiz', $html);
        $this->assertStringContainsString('Filho', $html);
        $this->assertStringContainsString('Neto', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, '<ul'));
        $this->assertStringContainsString('data-tree-toggle', $html);
    }

    public function testSetSelected(): void
    {
        $tree = new TTreeView;
        $tree->addItem('a', null, 'Raiz');
        $tree->addItem('b', 'a', 'Filho');
        $this->assertSame($tree, $tree->setSelected('b'));
        $html = $tree->getContents();
        $this->assertStringContainsString("data-selected='1'", $html);
        $this->assertStringContainsString("class='selected'", $html);
    }

    public function testAddItemWithAction(): void
    {
        $tree = new TTreeView;
        $action = new TAction(['Pagina', 'onVer'], ['id' => 1]);
        $tree->addItem('b', null, 'Com link', $action);
        $html = $tree->getContents();
        $this->assertStringContainsString('Com link', $html);
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $html);
    }
}
