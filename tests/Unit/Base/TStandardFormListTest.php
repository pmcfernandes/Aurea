<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use Aurea\Base\TStandardFormList;
use Aurea\Form\TForm;
use Aurea\Grid\TDataGrid;
use App\Model\Cliente;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardFormList */
final class TStandardFormListTest extends TestCase
{
    public function testBuildsFormFilterAndGridInOnePage(): void
    {
        $page = new class extends TStandardFormList {
            protected function getDatabase(): string { return 'app'; }
            protected function getActiveRecord(): string { return Cliente::class; }
            protected function getFilterFields(): array { return ['nome']; }
            protected function buildForm(): TForm { return new TForm('record'); }
            protected function buildFilter(): TForm { return new TForm('filter'); }
            protected function buildGrid(): TDataGrid { return new TDataGrid; }
        };
        $html = $page->getContents();
        $this->assertStringContainsString('record', $html);
        $this->assertStringContainsString('filter', $html);
        $this->assertStringContainsString('table-striped', $html);
    }
}
