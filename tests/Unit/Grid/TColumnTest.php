<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use App\Model\Cliente;
use Aurea\Core\TAction;
use Aurea\Grid\TColumn;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TColumn */
final class TColumnTest extends TestCase
{
    public function testGetNameGetLabel(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertSame('nome', $col->getName());
        $this->assertSame('Nome', $col->getLabel());
    }

    public function testSetActionGetAction(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertNull($col->getAction());
        $action = new TAction(['ClienteForm', 'onEdit']);
        $this->assertSame($col, $col->setAction($action));
        $this->assertSame($action, $col->getAction());
    }

    public function testSetAlignGetAlign(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertSame('left', $col->getAlign());
        $this->assertSame($col, $col->setAlign('right'));
        $this->assertSame('right', $col->getAlign());
    }

    public function testSetOrderableIsOrderable(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertFalse($col->isOrderable());
        $this->assertSame($col, $col->setOrderable());
        $this->assertTrue($col->isOrderable());
        $col->setOrderable(false);
        $this->assertFalse($col->isOrderable());
    }

    public function testSetTransformerCallable(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertSame($col, $col->setTransformer(fn($v) => strtoupper((string) $v)));
        $this->assertSame("<td class='text-left'>ANA</td>", $col->renderCell(['nome' => 'ana']));
    }

    public function testSetTotalCount(): void
    {
        $col = new TColumn('id', 'Id');
        $this->assertFalse($col->hasTotal());
        $col->setTotal('count');
        $this->assertTrue($col->hasTotal());
        $this->assertSame('3', $col->getTotalValue([['id' => 1], ['id' => 2], ['id' => 3]]));
    }

    public function testSetTotalSum(): void
    {
        $col = new TColumn('valor', 'Valor');
        $col->setTotal('sum');
        $this->assertTrue($col->hasTotal());
        $this->assertSame('30,50', $col->getTotalValue([['valor' => 10], ['valor' => 20.5]]));
    }

    public function testSetTotalCustomClosure(): void
    {
        $col = new TColumn('valor', 'Valor');
        $col->setTotal(fn(array $values) => 'n=' . count($values));
        $this->assertSame('n=2', $col->getTotalValue([['valor' => 1], ['valor' => 2]]));
    }

    public function testSetTotalInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TColumn('valor', 'Valor'))->setTotal('media');
    }

    public function testGetTotalValueWithoutTotalIsEmpty(): void
    {
        $this->assertSame('', (new TColumn('valor', 'Valor'))->getTotalValue([['valor' => 5]]));
    }

    public function testRenderCellWithRecord(): void
    {
        $col = new TColumn('nome', 'Nome');
        $record = new Cliente(['id' => 1, 'nome' => 'Ana']);
        $this->assertSame("<td class='text-left'>Ana</td>", $col->renderCell($record));
    }

    public function testRenderCellWithArray(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertSame("<td class='text-left'>Bruno</td>", $col->renderCell(['nome' => 'Bruno']));
    }

    public function testRenderCellEscapesValue(): void
    {
        $col = new TColumn('nome', 'Nome');
        $this->assertStringContainsString('&lt;b&gt;', $col->renderCell(['nome' => '<b>']));
    }

    public function testRenderCellWithLink(): void
    {
        $col = (new TColumn('nome', 'Nome'))->setAction(new TAction(['ClienteForm', 'onEdit']));
        $html = $col->renderCell(['id' => 7, 'nome' => 'Ana']);
        $this->assertStringContainsString('<a href=', $html);
        $this->assertStringContainsString('key=7', $html);
        $this->assertStringContainsString('Ana', $html);
    }

    public function testRenderCellWithoutLinkHasNoAnchor(): void
    {
        $html = (new TColumn('nome', 'Nome'))->renderCell(['id' => 7, 'nome' => 'Ana']);
        $this->assertStringNotContainsString('<a href=', $html);
    }
}
