<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Database\TRecord;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBEntry;
use PHPUnit\Framework\TestCase;

class WidgetPhaseRecord extends TRecord
{
    const TABLENAME = 'phase_widget_item';
    const PRIMARYKEY = 'id';
}

/** @covers \Aurea\Form\TDBEntry */
final class TDBEntryTest extends TestCase
{
    public const DB = 'testphase_dbentry';

    protected function setUp(): void
    {
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], self::DB);
        $pdo->exec('CREATE TABLE IF NOT EXISTS phase_widget_item (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(100))');
        $pdo->exec('DELETE FROM phase_widget_item');
        $pdo->exec("INSERT INTO phase_widget_item (name) VALUES ('Ana'), ('Bruno')");
        TTransaction::close();
    }

    public function testSearchAndRender(): void
    {
        $e = new TDBEntry('cliente', self::DB, WidgetPhaseRecord::class, 'name');
        $found = $e->search('An');
        $this->assertCount(1, $found);
        $this->assertSame('Ana', $found[0]['text']);
        $this->assertStringContainsString('datalist_cliente', $e->getContents());
    }

    public function testSearchPageHonoursMinimumLengthAndPagination(): void
    {
        $e = (new TDBEntry('cliente', self::DB, WidgetPhaseRecord::class, 'name'))->setMinLength(2);
        $this->assertSame([], $e->search('A'));
        $page = (new TDBEntry('cliente', self::DB, WidgetPhaseRecord::class, 'name'))->searchPage('n', 1, 1);
        $this->assertSame(1, $page['per_page']);
        $this->assertSame(2, $page['total']);
        $this->assertCount(1, $page['data']);
    }
}
