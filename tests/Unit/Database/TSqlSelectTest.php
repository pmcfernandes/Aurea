<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TConnection;
use Aurea\Database\TSqlSelect;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TSqlSelect */
final class TSqlSelectTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        $this->conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $this->conn->exec('CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, cidade TEXT)');
        $this->conn->exec("INSERT INTO item (nome, cidade) VALUES ('Ana', 'Porto'), ('Bia', 'Lx'), ('Cid', 'Porto')");
    }

    public function testSetEntityAddColumnSetCriteriaGetSql(): void
    {
        $c = (new TCriteria)->andWhere('cidade', '=', 'Porto');
        $sel = (new TSqlSelect)->setEntity('item')->addColumn('id')->addColumn('nome')->setCriteria($c);
        $this->assertSame('SELECT id, nome FROM item WHERE cidade = ?', $sel->getSql());
        $this->assertSame(['Porto'], $sel->getParams());
    }

    public function testGetSqlDefaultsStarAndOrderLimit(): void
    {
        $c = (new TCriteria)->setProperty('order', 'nome')->setProperty('direction', 'DESC')->setProperty('limit', 2);
        $sel = (new TSqlSelect)->setEntity('item')->setCriteria($c);
        $this->assertSame('SELECT * FROM item ORDER BY nome DESC LIMIT 2 OFFSET 0', $sel->getSql());
        $this->assertSame([], $sel->getParams());
    }

    public function testGetSqlMissingEntityThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TSqlSelect)->getSql();
    }

    public function testFetchAll(): void
    {
        $sel = (new TSqlSelect)->setEntity('item')
            ->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Porto'));
        $rows = $sel->fetchAll($this->conn);
        $this->assertCount(2, $rows);
    }

    public function testFetch(): void
    {
        $sel = (new TSqlSelect)->setEntity('item')
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Bia'));
        $row = $sel->fetch($this->conn);
        $this->assertSame('Bia', $row['nome'] ?? null);
        $none = (new TSqlSelect)->setEntity('item')
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Zzz'));
        $this->assertNull($none->fetch($this->conn));
    }

    public function testCount(): void
    {
        $sel = (new TSqlSelect)->setEntity('item');
        $this->assertSame(3, $sel->count($this->conn));
        $sel->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Lx'));
        $this->assertSame(1, $sel->count($this->conn));
    }
}
