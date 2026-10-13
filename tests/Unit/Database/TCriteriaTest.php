<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TCriteria */
final class TCriteriaTest extends TestCase
{
    public function testAddAndDumpOrderedParams(): void
    {
        $c = (new TCriteria)
            ->add(new TFilter('nome', '=', 'Ana'))
            ->add(new TFilter('cidade', '=', 'Porto'));
        [$where, $params] = $c->dump();
        $this->assertSame('WHERE nome = ? AND cidade = ?', $where);
        $this->assertSame(['Ana', 'Porto'], $params);
    }

    public function testAndWhereOrWhere(): void
    {
        $c = (new TCriteria)
            ->andWhere('a', '=', 1)
            ->orWhere('b', '=', 2);
        [$where, $params] = $c->dump();
        $this->assertSame('WHERE a = ? OR b = ?', $where);
        $this->assertSame([1, 2], $params);
    }

    public function testDumpEmpty(): void
    {
        [$where, $params] = (new TCriteria)->dump();
        $this->assertSame('', $where);
        $this->assertSame([], $params);
    }

    public function testInvalidLogicalOperatorThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TCriteria)->add(new TFilter('a', '=', 1), 'XOR');
    }

    public function testOrderClause(): void
    {
        $this->assertSame('', (new TCriteria)->orderClause());
        $this->assertSame(' ORDER BY nome ASC', (new TCriteria)->setProperty('order', 'nome')->orderClause());
        $c = (new TCriteria)->setProperty('order', 'nome')->setProperty('direction', 'desc');
        $this->assertSame(' ORDER BY nome DESC', $c->orderClause());
    }

    public function testLimitClause(): void
    {
        $this->assertSame('', (new TCriteria)->limitClause());
        $c = (new TCriteria)->setProperty('limit', 10)->setProperty('offset', 5);
        $this->assertSame(' LIMIT 10 OFFSET 5', $c->limitClause());
        $this->assertSame(' LIMIT 3 OFFSET 0', (new TCriteria)->setProperty('limit', 3)->limitClause());
    }

    public function testSetPropertiesGetProperty(): void
    {
        $c = (new TCriteria)->setProperties(['order' => 'nome', 'limit' => 5]);
        $this->assertSame('nome', $c->getProperty('order'));
        $this->assertSame(5, $c->getProperty('limit'));
        $this->assertNull($c->getProperty('missing'));
        $c->setProperty('offset', 2);
        $this->assertSame(2, $c->getProperty('offset'));
    }

    public function testResetProperties(): void
    {
        $c = (new TCriteria)->setProperties(['order' => 'nome', 'limit' => 5]);
        $c->resetProperties();
        $this->assertNull($c->getProperty('order'));
        $this->assertSame('', $c->orderClause());
        $this->assertSame('', $c->limitClause());
    }
}
