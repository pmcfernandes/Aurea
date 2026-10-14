<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TDataGridWrapper;
use Aurea\Core\TAction;
use Aurea\Grid\TDataGrid;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TDataGridWrapper */
final class TDataGridWrapperTest extends TestCase
{
    /** @var array */
    private array $getBackup = [];

    protected function setUp(): void
    {
        $this->getBackup = $_GET;
    }

    protected function tearDown(): void
    {
        $_GET = $this->getBackup;
    }

    public function testRendersWithoutSearch(): void
    {
        $wrapper = new TDataGridWrapper(new TDataGrid, 'Clientes');
        $html = $wrapper->getContents();
        $this->assertStringContainsString('Clientes', $html);
        $this->assertStringContainsString('card-header', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringNotContainsString('<form', $html);
    }

    public function testSetSearchAction(): void
    {
        $_GET = [];
        $wrapper = new TDataGridWrapper(new TDataGrid, 'Clientes');
        $action = new TAction(['ClienteList', 'onReload']);
        $this->assertSame($wrapper, $wrapper->setSearchAction($action));
        $html = $wrapper->getContents();
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString("name='q'", $html);
        $this->assertStringContainsString('Pesquisar...', $html);
    }

    public function testSetSearchActionCustomParam(): void
    {
        $_GET = ['busca' => 'ana'];
        $wrapper = new TDataGridWrapper(new TDataGrid, 'T');
        $wrapper->setSearchAction(new TAction(['ClienteList', 'onReload']), 'busca');
        $html = $wrapper->getContents();
        $this->assertStringContainsString("name='busca'", $html);
        $this->assertStringContainsString("value='ana'", $html);
    }

    public function testAddHeaderAction(): void
    {
        $wrapper = new TDataGridWrapper(new TDataGrid, 'T');
        $action = new TAction(['ClienteForm', 'onEdit']);
        $this->assertSame($wrapper, $wrapper->addHeaderAction('Novo', $action, 'fa-plus'));
        $html = $wrapper->getContents();
        $this->assertStringContainsString('Novo', $html);
        $this->assertStringContainsString('fa-plus', $html);
        $this->assertStringContainsString($action->toHref(), $html);
    }

    public function testSupportsHeaderWidgetsHeightAndAccessibleSearch(): void
    {
        $wrapper = new TDataGridWrapper(new TDataGrid, 'T');
        $wrapper->setSearchAction(new TAction(['ClienteList', 'onReload']))
            ->setSearchPlaceholder('Filtrar clientes')
            ->addHeaderWidget('<span>2 itens</span>')
            ->setHeight(320);
        $html = $wrapper->getContents();
        $this->assertStringContainsString("role='search'", $html);
        $this->assertStringContainsString('Filtrar clientes', $html);
        $this->assertStringContainsString('2 itens', $html);
        $this->assertStringContainsString('max-height:320px', $html);
    }
}
