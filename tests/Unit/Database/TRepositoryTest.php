<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use App\Model\Cliente;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TRepository */
final class TRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'db_repo_' . uniqid());
        TTransaction::get()->exec(
            'CREATE TABLE cliente (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, telefone TEXT, cidade TEXT)'
        );
        foreach ([['Ana', 'Porto'], ['Bia', 'Lx'], ['Cid', 'Porto']] as [$nome, $cidade]) {
            $c = new Cliente;
            $c->nome = $nome;
            $c->email = $nome . '@mail.com';
            $c->telefone = '1';
            $c->cidade = $cidade;
            $c->store();
        }
    }

    protected function tearDown(): void
    {
        try {
            TTransaction::rollbackAll();
        } catch (\Throwable) {
        }
        $r = new \ReflectionClass(TTransaction::class);
        foreach (['conns' => [], 'active' => null, 'logger' => null, 'dump' => false, 'loggerStrategy' => null, 'loggerFunction' => null] as $prop => $val) {
            $r->getProperty($prop)->setValue(null, $val);
        }
    }

    public function testInvalidClassThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TRepository(\stdClass::class);
    }

    public function testLoadAll(): void
    {
        $rows = (new TRepository(Cliente::class))->load();
        $this->assertCount(3, $rows);
        $this->assertContainsOnlyInstancesOf(Cliente::class, $rows);
    }

    public function testLoadWithCriteria(): void
    {
        $c = (new TCriteria)->andWhere('cidade', '=', 'Porto');
        $rows = (new TRepository(Cliente::class))->load($c);
        $this->assertCount(2, $rows);
    }

    public function testCount(): void
    {
        $repo = new TRepository(Cliente::class);
        $this->assertSame(3, $repo->count());
        $this->assertSame(1, $repo->count((new TCriteria)->andWhere('nome', '=', 'Bia')));
    }

    public function testDeleteWithCriteria(): void
    {
        $repo = new TRepository(Cliente::class);
        $n = $repo->delete((new TCriteria)->andWhere('cidade', '=', 'Porto'));
        $this->assertSame(2, $n);
        $this->assertSame(1, $repo->count());
    }
}
