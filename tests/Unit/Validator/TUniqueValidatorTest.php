<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Database\TTransaction;
use Aurea\Validator\TUniqueValidator;
use App\Model\Cliente;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TUniqueValidator */
final class TUniqueValidatorTest extends TestCase
{
    private string $db = '';
    private int $existingId = 0;

    protected function setUp(): void
    {
        $this->db = 'db_unique_' . uniqid();
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->db);
        TTransaction::get()->exec(
            'CREATE TABLE cliente (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, telefone TEXT, cidade TEXT)'
        );
        TTransaction::close();
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], $this->db);
        $c = new Cliente;
        $c->nome = 'Ana';
        $c->email = 'ana@mail.com';
        $c->telefone = '123';
        $c->cidade = 'Porto';
        $c->store();
        $this->existingId = (int) $c->id;
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

    public function testEmptyPasses(): void
    {
        $v = new TUniqueValidator($this->db, Cliente::class, 'email');
        $v->validate('Email', null);
        $v->validate('Email', '');
        $this->assertTrue(true);
    }

    public function testNewValuePasses(): void
    {
        (new TUniqueValidator($this->db, Cliente::class, 'email'))->validate('Email', 'novo@mail.com');
        $this->assertTrue(true);
    }

    public function testDuplicateThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TUniqueValidator($this->db, Cliente::class, 'email'))->validate('Email', 'ana@mail.com');
    }

    public function testOwnIdPasses(): void
    {
        $v = (new TUniqueValidator($this->db, Cliente::class, 'email'))->setExceptId($this->existingId);
        $v->validate('Email', 'ana@mail.com');
        $this->assertTrue(true);
    }

    public function testOtherIdThrows(): void
    {
        $this->expectException(\Exception::class);
        $v = (new TUniqueValidator($this->db, Cliente::class, 'email'))->setExceptId($this->existingId + 9999);
        $v->validate('Email', 'ana@mail.com');
    }
}
