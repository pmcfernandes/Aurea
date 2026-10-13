<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TDBLoop;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TDBLoop */
final class TDBLoopTest extends TestCase
{
    public function testIterateCountKey(): void
    {
        $loop = new TDBLoop([['id' => 1], ['id' => 2], ['id' => 3]]);
        $this->assertCount(3, $loop);
        $this->assertSame(0, $loop->key());
        $seen = [];
        foreach ($loop as $k => $row) {
            $seen[$k] = $row['id'];
        }
        $this->assertSame([1, 2, 3], array_values($seen));
    }

    public function testKeySetterAndValid(): void
    {
        $loop = new TDBLoop([['a' => 1], ['a' => 2]]);
        $this->assertSame(1, $loop->key(1));
        $this->assertTrue($loop->valid());
        $this->assertSame(['a' => 2], $loop->current());
        $loop->next();
        $this->assertFalse($loop->valid());
        $loop->rewind();
        $this->assertTrue($loop->valid());
        $this->assertSame(['a' => 1], $loop->current());
    }

    public function testEmpty(): void
    {
        $loop = new TDBLoop;
        $this->assertCount(0, $loop);
        $this->assertFalse($loop->valid());
        $this->assertNull($loop->current());
        $this->assertSame([], $loop->listColumns());
        $this->assertSame([], $loop->toArray());
    }

    public function testListColumnsArrayAndObject(): void
    {
        $loop = new TDBLoop([['id' => 1, 'nome' => 'A']]);
        $this->assertSame(['id', 'nome'], $loop->listColumns());
        $o = new \stdClass;
        $o->x = 1;
        $loop2 = new TDBLoop([$o]);
        $this->assertSame(['x'], $loop2->listColumns());
    }

    public function testFromQuery(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, nome VARCHAR(20))');
        $pdo->exec("INSERT INTO t (nome) VALUES ('A'), ('B')");
        $loop = TDBLoop::fromQuery($pdo, 'SELECT * FROM t WHERE nome = ?', ['B']);
        $this->assertCount(1, $loop);
        $this->assertSame('B', $loop->current()['nome']);
        $this->assertSame([['id' => 2, 'nome' => 'B']], array_values($loop->toArray()));
    }
}
