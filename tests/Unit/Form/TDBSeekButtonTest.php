<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBSeekButton;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBSeekButton */
final class TDBSeekButtonTest extends TestCase
{
    private const DB = 'tform_dbseekbutton';
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
        $f = new TDBSeekButton('busca', self::DB, Cliente::class, 'id', 'nome');
        $html = $f->getContents();
        $this->assertStringContainsString('<datalist', $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringContainsString('<button', $html);
    }

    public function testSetCriteriaFilters(): void
    {
        $f = new TDBSeekButton('busca', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($f, $f->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Porto')));
        $html = $f->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
    }

    public function testAddItemsPreventsReload(): void
    {
        $f = new TDBSeekButton('busca', self::DB, Cliente::class, 'id', 'nome');
        $f->addItems(['x' => 'Manual']);
        $html = $f->getContents();
        $this->assertStringContainsString('Manual', $html);
        $this->assertStringNotContainsString('Bruno', $html);
    }
}
