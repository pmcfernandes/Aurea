<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TConnection;
use Aurea\Database\TSqlInsert;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TSqlInsert */
final class TSqlInsertTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        $this->conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $this->conn->exec('CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, cidade TEXT)');
    }

    public function testSetRowDataGetSql(): void
    {
        $ins = (new TSqlInsert)->setEntity('item')->setRowData(['nome' => 'Ana', 'cidade' => 'Porto']);
        $this->assertSame('INSERT INTO item (nome, cidade) VALUES (?, ?)', $ins->getSql());
        $this->assertSame(['Ana', 'Porto'], $ins->getParams());
    }

    public function testAddRowData(): void
    {
        $ins = (new TSqlInsert)->setEntity('item')->addRowData('nome', 'Bia')->addRowData('cidade', 'Lx');
        $this->assertSame(['Bia', 'Lx'], $ins->getParams());
        $this->assertSame('INSERT INTO item (nome, cidade) VALUES (?, ?)', $ins->getSql());
    }

    public function testExecuteReturnsId(): void
    {
        $ins = (new TSqlInsert)->setEntity('item')->setRowData(['nome' => 'Ana', 'cidade' => 'Porto']);
        $id = $ins->execute($this->conn);
        $this->assertNotEmpty($id);
        $this->assertSame('Ana', $this->conn->query('SELECT nome FROM item WHERE id = ' . (int) $id)->fetchColumn());
    }

    public function testMissingEntityThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TSqlInsert)->setRowData(['nome' => 'x'])->getSql();
    }

    public function testMissingDataThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TSqlInsert)->setEntity('item')->getSql();
    }
}
