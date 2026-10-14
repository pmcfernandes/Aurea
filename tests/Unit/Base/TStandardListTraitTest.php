<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use App\Model\Cliente;
use Aurea\Base\TStandardList;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Form\TLabel;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardListTrait */
final class TStandardListTraitTest extends TestCase
{
    public const DB = 'testbase_listtrait';

    private array $postBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $_POST = [];
        $pdo = TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:'], self::DB);
        $pdo->exec('CREATE TABLE IF NOT EXISTS cliente (id INTEGER PRIMARY KEY AUTOINCREMENT, nome VARCHAR(100), email VARCHAR(100), telefone VARCHAR(30), cidade VARCHAR(60))');
        $pdo->exec('DELETE FROM cliente');
        TTransaction::close();
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        try {
            TTransaction::rollback();
        } catch (\Throwable) {
        }
    }

    private function makePage(): TStandardList
    {
        return new class extends TStandardList {
            protected function getDatabase(): string
            {
                return TStandardListTraitTest::DB;
            }

            protected function getActiveRecord(): string
            {
                return Cliente::class;
            }

            protected function getFilterFields(): array
            {
                return ['nome', 'cidade'];
            }

            protected function buildFilter(): TForm
            {
                $form = new TForm('filter_cliente');
                $form->addField(new TEntry('nome'));
                $form->addField(new TEntry('cidade'));
                return $form;
            }

            protected function buildGrid(): TDataGrid
            {
                $grid = new TDataGrid;
                $grid->addColumn(new TColumn('nome', 'Nome'));
                $grid->addColumn(new TColumn('cidade', 'Cidade'));
                return $grid;
            }
        };
    }

    private function seed(string $nome = 'Ana', string $cidade = 'Sintra'): int
    {
        TTransaction::open(self::DB);
        $obj = new Cliente(['nome' => $nome, 'email' => 'x@example.com', 'telefone' => '', 'cidade' => $cidade]);
        $obj->store();
        $id = (int) $obj->id;
        TTransaction::close();
        return $id;
    }

    public function testSettersAndBuildCriteria(): void
    {
        $page = $this->makePage();
        $page->setDefaultOrder('nome', 'desc');

        $ref = new \ReflectionMethod($page, 'buildCriteria');
        $ref->setAccessible(true);
        $criteria = $ref->invoke($page, ['nome' => 'An', 'page' => 2]);

        $this->assertSame('nome', $criteria->getProperty('order'));
        $this->assertSame('desc', $criteria->getProperty('direction'));
        $this->assertSame(10, $criteria->getProperty('limit'));
        $this->assertSame(10, $criteria->getProperty('offset'));
    }

    public function testBuildCriteriaRejectsUnknownSortFields(): void
    {
        $page = $this->makePage();
        $ref = new \ReflectionMethod($page, 'buildCriteria');
        $ref->setAccessible(true);
        $criteria = $ref->invoke($page, ['order' => 'email']);
        $this->assertSame('id', $criteria->getProperty('order'));

        $page->setOrderFields(['email']);
        $criteria = $ref->invoke($page, ['order' => 'email']);
        $this->assertSame('email', $criteria->getProperty('order'));
    }

    public function testOnDeleteWithoutConfirmAsksQuestion(): void
    {
        $page = $this->makePage();
        $id = $this->seed();

        ob_start();
        $page->onDelete(['key' => $id]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('Deseja excluir', $out);

        TTransaction::open(self::DB);
        $total = (new TRepository(Cliente::class))->count();
        TTransaction::close();
        $this->assertSame(1, $total);
    }

    public function testOnDeleteWithConfirmDeletes(): void
    {
        $page = $this->makePage();
        $id = $this->seed();

        ob_start();
        $page->onDelete(['key' => $id, 'confirm' => 1]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('exclu', strtolower($out));

        TTransaction::open(self::DB);
        $total = (new TRepository(Cliente::class))->count();
        TTransaction::close();
        $this->assertSame(0, $total);
    }

    public function testOnReloadLoadsItemsIntoGrid(): void
    {
        $page = $this->makePage();
        $this->seed('Ana');
        $this->seed('Bruno', 'POA');

        ob_start();
        $page->onReload([]);
        ob_end_clean();

        $this->assertStringContainsString('Ana', $page->getContents());
        $this->assertStringContainsString('Bruno', $page->getContents());
    }
}
