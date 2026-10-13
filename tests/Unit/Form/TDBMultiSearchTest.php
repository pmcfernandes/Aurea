<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBMultiSearch;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBMultiSearch */
final class TDBMultiSearchTest extends TestCase
{
    private const DB = 'tform_dbmultisearch';
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
        $f = new TDBMultiSearch('ms', self::DB, Cliente::class, 'id', 'nome');
        $html = $f->getContents();
        $this->assertStringContainsString('multisearch', $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
    }

    public function testSetCriteriaFilters(): void
    {
        $f = new TDBMultiSearch('ms', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($f, $f->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Porto')));
        $html = $f->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
    }

    public function testSetValueMarksChecked(): void
    {
        $f = new TDBMultiSearch('ms', self::DB, Cliente::class, 'nome', 'nome');
        $f->getContents();
        $f->setValue(['Ana']);
        $this->assertStringContainsString("value='Ana' checked", $f->getContents());
    }

    public function testLoadFailureThrows(): void
    {
        $f = new TDBMultiSearch('ms', 'tform_dbmultisearch_empty', Cliente::class, 'id', 'nome');
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'tform_dbmultisearch_empty');
        TTransaction::close();
        $this->expectException(\RuntimeException::class);
        $f->getContents();
    }
}
