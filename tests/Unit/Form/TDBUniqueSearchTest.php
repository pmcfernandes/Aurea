<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBUniqueSearch;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBUniqueSearch */
final class TDBUniqueSearchTest extends TestCase
{
    private const DB = 'tform_dbuniquesearch';
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->sessionBackup = $_SESSION ?? [];
        $this->seed();
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_SESSION = $this->sessionBackup;
        TTransaction::closeAll();
    }

    private function seed(): void
    {
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], self::DB);
        $pdo->exec('CREATE TABLE IF NOT EXISTS cliente (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, telefone TEXT, cidade TEXT)');
        $pdo->exec('DELETE FROM cliente');
        $pdo->exec("INSERT INTO cliente (nome, email, telefone, cidade) VALUES ('Ana','a@x.pt','1','Lisboa'),('Bruno','b@x.pt','2','Porto')");
        TTransaction::close();
    }

    public function testGetContentsLoadsFromDb(): void
    {
        $f = new TDBUniqueSearch('uni', self::DB, Cliente::class, 'id', 'nome');
        $html = $f->getContents();
        $this->assertStringContainsString('<datalist', $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
    }

    public function testSetCriteriaFilters(): void
    {
        $f = new TDBUniqueSearch('uni', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($f, $f->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Lisboa')));
        $html = $f->getContents();
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringNotContainsString('Bruno', $html);
    }

    public function testAddItemsPreventsReload(): void
    {
        $f = new TDBUniqueSearch('uni', self::DB, Cliente::class, 'id', 'nome');
        $f->addItems(['x' => 'Manual']);
        $html = $f->getContents();
        $this->assertStringContainsString('Manual', $html);
        $this->assertStringNotContainsString('Bruno', $html);
    }
}
