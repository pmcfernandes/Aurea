<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TConnection;
use Aurea\Database\TSqlMultiInsert;
use PHPUnit\Framework\TestCase;

final class TSqlMultiInsertTest extends TestCase
{
    public function testExecutesBatchInsert(): void
    {
        $pdo = TConnection::open(['type' => 'sqlite', 'name' => ':memory:']);
        $pdo->exec('CREATE TABLE item (id INTEGER PRIMARY KEY, name TEXT)');
        $insert = (new TSqlMultiInsert)->setEntity('item')->setRows([['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']]);
        $this->assertSame(2, $insert->execute($pdo));
        $this->assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM item')->fetchColumn());
    }

    public function testRejectsMismatchedColumns(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TSqlMultiInsert)->setRows([['a' => 1], ['b' => 2]]);
    }
}
