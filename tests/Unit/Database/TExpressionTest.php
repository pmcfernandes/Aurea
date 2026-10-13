<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TExpression;
use Aurea\Database\TFilter;
use Aurea\Database\TTransaction;
use PDO;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TExpression */
final class TExpressionTest extends TestCase
{
    public function testGetSqlGetParams(): void
    {
        $e = new TExpression('UPPER(nome)', ['x']);
        $this->assertSame('UPPER(nome)', $e->getSql());
        $this->assertSame(['x'], $e->getParams());
    }

    public function testGetParamsDefaultEmpty(): void
    {
        $this->assertSame([], (new TExpression('NOW()'))->getParams());
    }

    public function testUseInsideFilterDump(): void
    {
        $params = [];
        $sql = (new TFilter(new TExpression('UPPER(nome)'), '=', 'ANA'))->dump($params);
        $this->assertSame('UPPER(nome) = ?', $sql);
        $this->assertSame(['ANA'], $params);
    }

    public function testRealExecutionUpper(): void
    {
        $conn = new PDO('sqlite::memory:');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE TABLE p (id INTEGER PRIMARY KEY, nome TEXT)");
        $conn->exec("INSERT INTO p (nome) VALUES ('ana')");
        $params = [];
        $expr = (new TFilter(new TExpression('UPPER(nome)'), '=', 'ANA'))->dump($params);
        $stmt = $conn->prepare("SELECT nome FROM p WHERE {$expr}");
        $stmt->execute($params);
        $this->assertSame('ana', $stmt->fetchColumn());
    }
}
