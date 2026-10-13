<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBCheckGroup;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBCheckGroup */
final class TDBCheckGroupTest extends TestCase
{
    private const DB = 'tform_dbcheckgroup';
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
        $g = new TDBCheckGroup('clis', self::DB, Cliente::class, 'id', 'nome');
        $html = $g->getContents();
        $this->assertStringContainsString("type='checkbox'", $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
    }

    public function testSetCriteriaFilters(): void
    {
        $g = new TDBCheckGroup('clis', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($g, $g->setCriteria((new TCriteria)->andWhere('cidade', '=', 'Porto')));
        $html = $g->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
    }

    public function testSetValueMarksChecked(): void
    {
        $g = new TDBCheckGroup('clis', self::DB, Cliente::class, 'nome', 'nome');
        $g->getContents();
        $g->setValue(['Ana']);
        $this->assertStringContainsString("value='Ana' checked", $g->getContents());
    }

    public function testLoadFailureThrows(): void
    {
        $g = new TDBCheckGroup('clis', 'tform_dbcheckgroup_empty', Cliente::class, 'id', 'nome');
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'tform_dbcheckgroup_empty');
        TTransaction::close();
        $this->expectException(\RuntimeException::class);
        $g->getContents();
    }
}
