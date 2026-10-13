<?php
declare(strict_types=1);

namespace Tests\Unit\Database;

use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use App\Model\Cliente;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Database\TRecord */
final class TRecordTest extends TestCase
{
    private string $db = '';

    protected function setUp(): void
    {
        $this->db = 'db_rec_' . uniqid();
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->db);
        TTransaction::get()->exec(
            'CREATE TABLE cliente (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, telefone TEXT, cidade TEXT)'
        );
        TTransaction::close();
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->db);
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

    private function makeCliente(string $nome = 'Ana'): Cliente
    {
        $c = new Cliente;
        $c->nome = $nome;
        $c->email = strtolower($nome) . '@mail.com';
        $c->telefone = '123';
        $c->cidade = 'Porto';
        return $c;
    }

    public function testStoreAndFind(): void
    {
        $c = $this->makeCliente();
        $c->store();
        $this->assertNotEmpty($c->id);
        $found = Cliente::find((int) $c->id);
        $this->assertSame('Ana', $found->nome);
    }

    public function testUpdate(): void
    {
        $c = $this->makeCliente();
        $c->store();
        $c->nome = 'Bia';
        $c->store();
        $this->assertSame('Bia', Cliente::find((int) $c->id)->nome);
    }

    public function testWhereGetFirstCount(): void
    {
        $this->makeCliente('Ana')->store();
        $this->makeCliente('Bia')->store();
        $rows = Cliente::where('cidade', '=', 'Porto')->get();
        $this->assertCount(2, $rows);
        $first = Cliente::where('nome', '=', 'Bia')->first();
        $this->assertNotNull($first);
        $this->assertSame('Bia', $first->nome);
        $this->assertSame(1, Cliente::where('nome', '=', 'Ana')->count());
        $this->assertNull(Cliente::where('nome', '=', 'Zzz')->first());
    }

    public function testAllAndCountWithCriteria(): void
    {
        $this->makeCliente('Ana')->store();
        $this->makeCliente('Bia')->store();
        $this->assertCount(2, Cliente::all());
        $c = (new TCriteria)->andWhere('nome', '=', 'Ana');
        $this->assertCount(1, Cliente::all($c));
    }

    public function testFromArrayToArrayGetPrimaryKeyValue(): void
    {
        $c = new Cliente;
        $c->fromArray(['nome' => 'Ana', 'email' => 'a@b.c', 'telefone' => '1', 'cidade' => 'Lx']);
        $this->assertSame('Ana', $c->toArray()['nome']);
        $this->assertNull($c->getPrimaryKeyValue());
        $c->store();
        $this->assertSame((int) $c->id, $c->getPrimaryKeyValue());
    }

    public function testDeleteById(): void
    {
        $c = $this->makeCliente();
        $c->store();
        $id = (int) $c->id;
        $c->delete();
        $this->expectException(\RuntimeException::class);
        Cliente::find($id);
    }

    public function testDeleteWithWhere(): void
    {
        $this->makeCliente('Ana')->store();
        $this->makeCliente('Bia')->store();
        $deleted = Cliente::where('nome', '=', 'Ana')->delete();
        $this->assertSame(1, $deleted);
        $this->assertCount(1, Cliente::all());
    }

    public function testRollback(): void
    {
        $c = $this->makeCliente('Tmp');
        $c->store();
        TTransaction::rollback();
        $this->assertSame([], Cliente::all());
    }
}
