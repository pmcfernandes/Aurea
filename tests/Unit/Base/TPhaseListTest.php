<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use Aurea\Base\TStandardListTrait;
use Aurea\Core\TSession;
use Aurea\Database\TRecord;
use Aurea\Database\TTransaction;
use PHPUnit\Framework\TestCase;

class PhaseListRecord extends TRecord
{
    const TABLENAME = 'phase_list_item';
    const PRIMARYKEY = 'id';
}

class PhaseListPage
{
    use TStandardListTrait;

    public function __construct()
    {
        $this->setDatabase(TPhaseListTest::DB);
        $this->setActiveRecord(PhaseListRecord::class);
        $this->setFilterFields(['name']);
    }

    public function exposeBuild(array $param)
    {
        return $this->buildCriteria($param);
    }
}

/** @covers \Aurea\Base\TStandardListTrait */
final class TPhaseListTest extends TestCase
{
    public const DB = 'testphase_list';

    protected function setUp(): void
    {
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], self::DB);
        $pdo->exec('CREATE TABLE IF NOT EXISTS phase_list_item (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(100))');
        $pdo->exec('DELETE FROM phase_list_item');
        TTransaction::close();
        TSession::free();
    }

    public function testPersistentFilters(): void
    {
        $page = new PhaseListPage();
        $page->setPersistentFilters(true);
        $page->exposeBuild(['name' => 'Ana']);
        $criteria = $page->exposeBuild([]);
        [$where, $params] = $criteria->dump();
        $this->assertStringContainsString('WHERE', $where);
        $this->assertSame(['%Ana%'], $params);
        $page->clearFilters();
        $criteria = $page->exposeBuild([]);
        [$where] = $criteria->dump();
        $this->assertSame('', $where);
    }

    public function testDeleteCollection(): void
    {
        TTransaction::open(self::DB);
        $a = new PhaseListRecord(['name' => 'A']);
        $a->store();
        $b = new PhaseListRecord(['name' => 'B']);
        $b->store();
        TTransaction::close();
        $page = new PhaseListPage();
        $total = $page->deleteCollection([(int) $a->id, (int) $b->id]);
        $this->assertSame(2, $total);
    }
}
