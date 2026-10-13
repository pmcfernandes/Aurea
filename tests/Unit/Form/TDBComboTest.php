<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TTransaction;
use Aurea\Form\TDBCombo;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDBCombo */
final class TDBComboTest extends TestCase
{
    private const DB = 'tform_dbcombo';
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
        $pdo->exec("INSERT INTO cliente (nome, email, telefone, cidade) VALUES ('Zeca','z@x.pt','3','Faro'),('Bruno','b@x.pt','2','Porto'),('Ana','a@x.pt','1','Lisboa')");
        TTransaction::close();
    }

    public function testGetContentsLoadsFromDb(): void
    {
        $combo = new TDBCombo('cli', self::DB, Cliente::class, 'id', 'nome');
        $html = $combo->getContents();
        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringContainsString('Zeca', $html);
    }

    public function testOrderColumnSorts(): void
    {
        $combo = new TDBCombo('cli', self::DB, Cliente::class, 'id', 'nome', 'nome');
        $html = $combo->getContents();
        $posAna = strpos($html, '>Ana</option>');
        $posBruno = strpos($html, '>Bruno</option>');
        $posZeca = strpos($html, '>Zeca</option>');
        $this->assertNotFalse($posAna);
        $this->assertNotFalse($posBruno);
        $this->assertNotFalse($posZeca);
        $this->assertLessThan($posBruno, $posAna);
        $this->assertLessThan($posZeca, $posBruno);
    }

    public function testSetCriteriaFilters(): void
    {
        $combo = new TDBCombo('cli', self::DB, Cliente::class, 'id', 'nome');
        $criteria = (new TCriteria)->andWhere('cidade', '=', 'Porto');
        $this->assertSame($combo, $combo->setCriteria($criteria));
        $html = $combo->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
        $this->assertStringNotContainsString('Zeca', $html);
    }

    public function testSecondGetContentsUsesCache(): void
    {
        $combo = new TDBCombo('cli', self::DB, Cliente::class, 'id', 'nome');
        $this->assertSame($combo->getContents(), $combo->getContents());
    }

    public function testLoadFailureThrows(): void
    {
        $combo = new TDBCombo('cli', 'tform_dbcombo_empty', Cliente::class, 'id', 'nome');
        TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], 'tform_dbcombo_empty');
        TTransaction::close();
        $this->expectException(\RuntimeException::class);
        $combo->getContents();
    }
}
