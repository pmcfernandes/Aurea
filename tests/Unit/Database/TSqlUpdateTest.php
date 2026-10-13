<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TConnection;
use Aurea\Database\TSqlUpdate;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TSqlUpdate */
final class TSqlUpdateTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        $this->conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $this->conn->exec('CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, cidade TEXT)');
        $this->conn->exec("INSERT INTO item (nome, cidade) VALUES ('Ana', 'Porto'), ('Bia', 'Lx')");
    }

    public function testGetSqlGetParams(): void
    {
        $upd = (new TSqlUpdate)->setEntity('item')->setRowData(['cidade' => 'Braga'])
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Ana'));
        $this->assertSame('UPDATE item SET cidade = ? WHERE nome = ?', $upd->getSql());
        $this->assertSame(['Braga', 'Ana'], $upd->getParams());
    }

    public function testExecuteReturnsRows(): void
    {
        $upd = (new TSqlUpdate)->setEntity('item')->setRowData(['cidade' => 'Braga'])
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Ana'));
        $this->assertSame(1, $upd->execute($this->conn));
        $this->assertSame('Braga', $this->conn->query("SELECT cidade FROM item WHERE nome = 'Ana'")->fetchColumn());
    }

    public function testExecuteWithoutCriteriaUpdatesAll(): void
    {
        $upd = (new TSqlUpdate)->setEntity('item')->setRowData(['cidade' => 'X']);
        $this->assertSame('UPDATE item SET cidade = ?', $upd->getSql());
        $this->assertSame(2, $upd->execute($this->conn));
    }

    public function testMissingEntityThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TSqlUpdate)->setRowData(['a' => 1])->getSql();
    }

    public function testMissingDataThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TSqlUpdate)->setEntity('item')->getSql();
    }
}
