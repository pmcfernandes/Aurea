<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TConnection;
use Aurea\Database\TSqlDelete;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TSqlDelete */
final class TSqlDeleteTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        $this->conn = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $this->conn->exec('CREATE TABLE item (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT)');
        $this->conn->exec("INSERT INTO item (nome) VALUES ('Ana'), ('Bia')");
    }

    public function testGetSqlGetParams(): void
    {
        $del = (new TSqlDelete)->setEntity('item')
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Ana'));
        $this->assertSame('DELETE FROM item WHERE nome = ?', $del->getSql());
        $this->assertSame(['Ana'], $del->getParams());
    }

    public function testExecute(): void
    {
        $del = (new TSqlDelete)->setEntity('item')
            ->setCriteria((new TCriteria)->andWhere('nome', '=', 'Ana'));
        $this->assertSame(1, $del->execute($this->conn));
        $this->assertSame(1, (int) $this->conn->query('SELECT COUNT(*) FROM item')->fetchColumn());
    }

    public function testWithoutCriteriaThrows(): void
    {
        $del = (new TSqlDelete)->setEntity('item');
        $this->expectException(\RuntimeException::class);
        $del->getSql();
    }

    public function testWithoutCriteriaGetParamsThrows(): void
    {
        $del = (new TSqlDelete)->setEntity('item');
        $this->expectException(\RuntimeException::class);
        $del->getParams();
    }

    public function testWithoutCriteriaExecuteThrows(): void
    {
        $del = (new TSqlDelete)->setEntity('item');
        $this->expectException(\RuntimeException::class);
        $del->execute($this->conn);
    }

    public function testEmptyCriteriaThrows(): void
    {
        $del = (new TSqlDelete)->setEntity('item')->setCriteria(new TCriteria);
        $this->expectException(\RuntimeException::class);
        $del->getSql();
    }

    public function testMissingEntityThrows(): void
    {
        $del = (new TSqlDelete)->setCriteria((new TCriteria)->andWhere('nome', '=', 'Ana'));
        $this->expectException(\RuntimeException::class);
        $del->getSql();
    }
}
