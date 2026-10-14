<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TFilter;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TFilter */
final class TFilterTest extends TestCase
{
    public function testRejectsUnsafeIdentifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TFilter('nome; DROP TABLE cliente', '=', 'Ana');
    }

    public function testAcceptsQualifiedIdentifier(): void
    {
        $filter = new TFilter('cliente.nome', '=', 'Ana');
        $params = [];
        $this->assertSame('cliente.nome = ?', $filter->dump($params));
        $this->assertSame(['Ana'], $params);
    }

    public function testDumpEquals(): void
    {
        $params = [];
        $sql = (new TFilter('nome', '=', 'Ana'))->dump($params);
        $this->assertSame('nome = ?', $sql);
        $this->assertSame(['Ana'], $params);
    }

    public function testDumpLike(): void
    {
        $params = [];
        $sql = (new TFilter('nome', 'LIKE', 'An%'))->dump($params);
        $this->assertSame('nome LIKE ?', $sql);
        $this->assertSame(['An%'], $params);
    }

    public function testDumpIn(): void
    {
        $params = [];
        $sql = (new TFilter('id', 'IN', [1, 2, 3]))->dump($params);
        $this->assertSame('id IN (?,?,?)', $sql);
        $this->assertSame([1, 2, 3], $params);
    }

    public function testDumpNotIn(): void
    {
        $params = [];
        $sql = (new TFilter('id', 'NOT IN', [4, 5]))->dump($params);
        $this->assertSame('id NOT IN (?,?)', $sql);
        $this->assertSame([4, 5], $params);
    }

    public function testDumpIsNull(): void
    {
        $params = [];
        $sql = (new TFilter('email', 'IS', null))->dump($params);
        $this->assertSame('email IS NULL', $sql);
        $this->assertSame([], $params);
    }

    public function testDumpIsNotNull(): void
    {
        $params = [];
        $sql = (new TFilter('email', 'IS NOT', null))->dump($params);
        $this->assertSame('email IS NOT NULL', $sql);
        $this->assertSame([], $params);
    }

    public function testInvalidOperatorThrows(): void
    {
        $params = [];
        $this->expectException(\RuntimeException::class);
        (new TFilter('nome', 'BOGUS', 'x'))->dump($params);
    }
}
