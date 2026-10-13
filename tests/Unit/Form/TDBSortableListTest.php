<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBSortableList;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBSortableList */
final class TDBSortableListTest extends TestCase
{
    private const DB = 'tform_dbsortablelist';
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
        $f = new TDBSortableList('ordem', self::DB, Cliente::class, 'id', 'nome');
        $html = $f->getContents();
        $this->assertStringContainsString('<li', $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
    }

    public function testSetCriteriaFilters(): void
    {
        $f = new TDBSortableList('ordem', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($f, $f->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Lisboa')));
        $html = $f->getContents();
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringNotContainsString('Bruno', $html);
    }

    public function testSetValueReorders(): void
    {
        $f = new TDBSortableList('ordem', self::DB, Cliente::class, 'nome', 'nome');
        $f->getContents();
        $f->setValue(['Bruno', 'Ana']);
        $html = $f->getContents();
        $posBruno = strpos($html, '>Bruno</li>');
        $posAna = strpos($html, '>Ana</li>');
        $this->assertNotFalse($posBruno);
        $this->assertNotFalse($posAna);
        $this->assertLessThan($posAna, $posBruno);
    }

    public function testLoadFailureThrows(): void
    {
        $f = new TDBSortableList('ordem', 'tform_dbsortablelist_empty', Cliente::class, 'id', 'nome');
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'tform_dbsortablelist_empty');
        TTransaction::close();
        $this->expectException(\RuntimeException::class);
        $f->getContents();
    }
}
