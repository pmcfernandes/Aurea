<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use Aurea\Core\TAction;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use Aurea\Grid\TRowActionGroup;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TDataGrid */
final class TDataGridTest extends TestCase
{
    private array $postBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
    }

    public function testAddColumnRendersHeader(): void
    {
        $grid = new TDataGrid;
        $this->assertSame($grid, $grid->addColumn(new TColumn('nome', 'Nome')));
        $this->assertStringContainsString('Nome', $grid->getContents());
    }

    public function testAddItemAndSetItems(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $grid->addItem(['nome' => 'Ana']);
        $grid->addItem(['nome' => 'Bruno']);
        $html = $grid->getContents();
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);

        $grid->setItems([['nome' => 'Carla']]);
        $html = $grid->getContents();
        $this->assertStringNotContainsString('Ana', $html);
        $this->assertStringContainsString('Carla', $html);
    }

    public function testClear(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $grid->setItems([['nome' => 'Ana']])->clear();
        $this->assertStringContainsString('Nenhum registro encontrado', $grid->getContents());
    }

    public function testEmptyGridMessage(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $html = $grid->getContents();
        $this->assertStringContainsString('Nenhum registro encontrado', $html);
        $this->assertStringNotContainsString('<tfoot', $html);
    }

    public function testSetOrderHeaderCellWithArrows(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn((new TColumn('nome', 'Nome'))->setOrderable());
        $grid->addColumn(new TColumn('email', 'E-mail'));

        $grid->setOrder('nome', 'asc', new TAction(['FakeList', 'onReload']));
        $html = $grid->getContents();
        $this->assertStringContainsString('▲', $html);
        $this->assertStringContainsString('order=nome', $html);
        $this->assertStringContainsString('direction=desc', $html);

        $grid->setOrder('nome', 'desc', new TAction(['FakeList', 'onReload']));
        $this->assertStringContainsString('▼', $grid->getContents());
    }

    public function testNonOrderableColumnHasNoLink(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('email', 'E-mail'));
        $grid->setOrder('email', 'asc', new TAction(['FakeList', 'onReload']));
        $this->assertStringContainsString('<th>E-mail</th>', $grid->getContents());
    }

    public function testAddActionRendersRowButton(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $this->assertSame(
            $grid,
            $grid->addAction('Editar', new TAction(['ClienteForm', 'onEdit']), 'fa-pen', 'btn-outline-primary')
        );
        $grid->addItem(['id' => 4, 'nome' => 'Ana']);
        $html = $grid->getContents();
        $this->assertStringContainsString('Ações', $html);
        $this->assertStringContainsString('fa-pen', $html);
        $this->assertStringContainsString('key=4', $html);
    }

    public function testAddActionWithConditionalDisplay(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $grid->addAction('Sempre', new TAction(['A', 'onX']));
        $grid->addAction('Condicional', new TAction(['A', 'onY']), 'fa-edit', 'btn-outline-primary', fn(array $row) => ($row['ativo'] ?? 0) == 1);
        $grid->setItems([['id' => 1, 'nome' => 'Off', 'ativo' => 0], ['id' => 2, 'nome' => 'On', 'ativo' => 1]]);
        $html = $grid->getContents();
        $this->assertSame(2, substr_count($html, 'onX'));
        $this->assertSame(1, substr_count($html, 'onY'));
    }

    public function testAddActionGroupWithConditionalDisplay(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $group = new TRowActionGroup('Opções');
        $group->addAction('Ver', new TAction(['A', 'onView']));
        $group->addAction('Oculta', new TAction(['A', 'onHidden']), '', fn(array $row) => false);
        $this->assertSame($grid, $grid->addActionGroup($group));
        $grid->addItem(['id' => 9, 'nome' => 'Ana']);
        $html = $grid->getContents();
        $this->assertStringContainsString('Opções', $html);
        $this->assertStringContainsString('onView', $html);
        $this->assertStringNotContainsString('onHidden', $html);
    }

    public function testEnableSelectionAndGetSelectedIds(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $this->assertSame($grid, $grid->enableSelection());
        $grid->addItem(['id' => 3, 'nome' => 'Ana']);
        $html = $grid->getContents();
        $this->assertStringContainsString('name="_grid_sel[]"', $html);
        $this->assertStringContainsString('value="3"', $html);

        $_POST['_grid_sel'] = ['3', '5'];
        $this->assertSame(['3', '5'], TDataGrid::getSelectedIds());
    }

    public function testGetSelectedIdsEmptyAndInvalid(): void
    {
        $this->assertSame([], TDataGrid::getSelectedIds());
        $_POST['_grid_sel'] = 'not-an-array';
        $this->assertSame([], TDataGrid::getSelectedIds());
    }

    public function testGetContentsWithFooter(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn((new TColumn('id', 'Id'))->setTotal('count'));
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $grid->setItems([['id' => 1, 'nome' => 'Ana'], ['id' => 2, 'nome' => 'Bruno']]);
        $html = $grid->getContents();
        $this->assertStringContainsString('<tfoot', $html);
        $this->assertStringContainsString('>2<', $html);
    }

    public function testGetContentsWithoutFooterWhenNoTotals(): void
    {
        $grid = new TDataGrid;
        $grid->addColumn(new TColumn('nome', 'Nome'));
        $grid->setItems([['nome' => 'Ana']]);
        $this->assertStringNotContainsString('<tfoot', $grid->getContents());
    }
}
