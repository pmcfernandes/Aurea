<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use Aurea\Base\TMasterDetailTrait;
use Aurea\Database\TRecord;
use Aurea\Database\TTransaction;
use PHPUnit\Framework\TestCase;

final class TMasterDetailTraitTest extends TestCase
{
    public function testReplacesDetailRowsInsideActiveTransaction(): void
    {
        $name = 'master_detail_' . uniqid();
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $name);
        $pdo->exec('CREATE TABLE master_item (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $pdo->exec('CREATE TABLE detail_item (id INTEGER PRIMARY KEY AUTOINCREMENT, master_id INTEGER, value TEXT, ignored TEXT)');

        $master = new class(['name' => 'Mestre']) extends TRecord { public const TABLENAME = 'master_item'; };
        $master->store();
        $detailClass = new class extends TRecord { public const TABLENAME = 'detail_item'; };
        $service = new class {
            use TMasterDetailTrait;
            public function replace(TRecord $master, string $detail, array $rows): void { $this->replaceDetails($master, $detail, 'master_id', $rows, ['value']); }
        };
        $service->replace($master, $detailClass::class, [['value' => 'A', 'ignored' => 'x'], ['value' => 'B']]);
        $this->assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM detail_item')->fetchColumn());
        $this->assertSame('', (string) $pdo->query('SELECT ignored FROM detail_item LIMIT 1')->fetchColumn());
        TTransaction::rollback();
    }
}
