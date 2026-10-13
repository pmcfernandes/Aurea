<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TTable;
use Aurea\Container\TTableCell;
use Aurea\Container\TTableRow;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aurea\Container\TTable
 * @covers \Aurea\Container\TTableRow
 * @covers \Aurea\Container\TTableCell
 */
final class TTableTest extends TestCase
{
    public function testAddRowReturnsRow(): void
    {
        $table = new TTable;
        $row = $table->addRow();
        $this->assertInstanceOf(TTableRow::class, $row);
        $this->assertStringContainsString('<tr></tr>', $table->getContents());
    }

    public function testAddCellReturnsCell(): void
    {
        $row = new TTableRow;
        $cell = $row->addCell('valor');
        $this->assertInstanceOf(TTableCell::class, $cell);
        $this->assertStringContainsString('<td>valor</td>', $row->getContents());
    }

    public function testFullTableRendering(): void
    {
        $table = new TTable;
        $table->addRow()->addCell('a');
        $table->addRow()->addCell('b');
        $html = $table->getContents();
        $this->assertStringContainsString('class="table"', $html);
        $this->assertStringContainsString('<td>a</td>', $html);
        $this->assertStringContainsString('<td>b</td>', $html);
        $this->assertSame(2, substr_count($html, '<tr>'));
    }
}
