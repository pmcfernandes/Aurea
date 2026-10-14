<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Database\TMigrator;
use Aurea\Database\TRecord;
use Aurea\Database\TRepository;
use Aurea\Database\TSqlSelect;
use Aurea\Database\TTransaction;
use PHPUnit\Framework\TestCase;

class Phase2Record extends TRecord
{
    const TABLENAME = 'phase2_item';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';
    const SOFT_DELETE = true;
}

class Phase2Owner extends TRecord
{
    const TABLENAME = 'phase2_owner';
    const PRIMARYKEY = 'id';
}

/** @covers \Aurea\Database\TRecord */
final class TPhase2OrmTest extends TestCase
{
    public const DB = 'testphase2_orm';

    protected function setUp(): void
    {
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], self::DB);
        $pdo->exec('CREATE TABLE IF NOT EXISTS phase2_item (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(100), created_at VARCHAR(30), updated_at VARCHAR(30), deleted_at VARCHAR(30))');
        $pdo->exec('DELETE FROM phase2_item');
        $pdo->exec('CREATE TABLE IF NOT EXISTS phase2_owner (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(100), item_id INTEGER)');
        $pdo->exec('DELETE FROM phase2_owner');
        $pdo->exec('CREATE TABLE IF NOT EXISTS phase2_pivot (owner_id INTEGER, item_id INTEGER)');
        $pdo->exec('DELETE FROM phase2_pivot');
        TTransaction::close();
    }

    public function testTimestampsAndSoftDelete(): void
    {
        TTransaction::open(self::DB);
        $obj = new Phase2Record(['name' => 'A']);
        $obj->store();
        $this->assertNotEmpty($obj->created_at);
        $this->assertNotEmpty($obj->updated_at);
        $id = (int) $obj->id;
        $obj->delete();
        $this->assertTrue($obj->isTrashed());
        $found = Phase2Record::find($id, true);
        $this->assertTrue($found->isTrashed());
        $found->restore();
        $this->assertFalse($found->isTrashed());
        TTransaction::close();
    }

    public function testRelations(): void
    {
        TTransaction::open(self::DB);
        $item = new Phase2Record(['name' => 'Item1']);
        $item->store();
        $owner = new Phase2Owner(['name' => 'Owner1', 'item_id' => $item->id]);
        $owner->store();
        $this->assertSame('Item1', $owner->belongsTo(Phase2Record::class, 'item_id')?->name);
        $children = $item->hasMany(Phase2Owner::class, 'item_id');
        $this->assertCount(1, $children);
        $pdo = TTransaction::get();
        $pdo->exec("INSERT INTO phase2_pivot (owner_id, item_id) VALUES ({$owner->id}, {$item->id})");
        $many = $owner->belongsToMany(Phase2Record::class, 'phase2_pivot', 'owner_id', 'item_id');
        $this->assertCount(1, $many);
        TTransaction::close();
    }

    public function testRepositoryAggregatesAndPaginate(): void
    {
        TTransaction::open(self::DB);
        foreach (['A', 'B', 'C'] as $name) {
            (new Phase2Record(['name' => $name]))->store();
        }
        $repo = new TRepository(Phase2Record::class);
        $this->assertSame(3, $repo->count());
        $this->assertNotNull($repo->max('id'));
        $this->assertNotNull($repo->min('id'));
        $this->assertNotNull($repo->first());
        $page = $repo->paginate(null, 1, 2);
        $this->assertSame(3, $page['total']);
        $this->assertCount(2, $page['data']);
        $this->assertSame(2, $page['last_page']);
        TTransaction::close();
    }

    public function testCriteriaGroupHavingAndSelectJoins(): void
    {
        $c = (new TCriteria)->setGroup('cidade')->setHaving('COUNT(*) > ?', [1])->setDistinct()->setColumns(['id', 'nome']);
        $this->assertStringContainsString('GROUP BY', $c->groupClause());
        [$having, $params] = $c->havingClause();
        $this->assertStringContainsString('HAVING', $having);
        $this->assertSame([1], $params);

        $sel = (new TSqlSelect)->setEntity('cliente')->addColumn('cliente.*')->addJoin('LEFT JOIN cidade c ON c.id = cliente.cidade_id');
        $sel->setDistinct()->setGroup('cidade_id');
        $this->assertStringContainsString('LEFT JOIN', $sel->getSql());
        $this->assertStringContainsString('DISTINCT', $sel->getSql());
    }

    public function testTransactionHelperAndMigratorRollback(): void
    {
        $result = TTransaction::transaction(fn() => 42, self::DB);
        $this->assertSame(42, $result);
        try {
            TTransaction::transaction(function () {
                throw new \RuntimeException('fail');
            }, self::DB);
            $this->fail('deveria falhar');
        } catch (\RuntimeException) {
            $this->assertTrue(true);
        }

        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'testphase2_mig_' . uniqid());
        $m = new TMigrator($pdo);
        $m->add('001', 'CREATE TABLE t1 (id INTEGER PRIMARY KEY)');
        $m->addRollback('001', 'DROP TABLE t1');
        $this->assertSame(['001'], $m->migrate());
        $this->assertSame('001', $m->rollback());
        $this->assertSame([], $m->applied());
    }
}
