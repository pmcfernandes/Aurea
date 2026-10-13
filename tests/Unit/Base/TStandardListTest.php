<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use App\Model\Cliente;
use Aurea\Base\TStandardList;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardList */
final class TStandardListTest extends TestCase
{
    public const DB = 'testbase_standardlist';

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
                return TStandardListTest::DB;
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

    private function seed(string $nome, string $cidade = 'Sintra'): int
    {
        TTransaction::open(self::DB);
        $obj = new Cliente(['nome' => $nome, 'email' => 'x@example.com', 'telefone' => '', 'cidade' => $cidade]);
        $obj->store();
        $id = (int) $obj->id;
        TTransaction::close();
        return $id;
    }

    public function testConstructorBindsConfiguration(): void
    {
        $page = $this->makePage();
        $ref = new \ReflectionClass($page);

        $db = $ref->getProperty('database');
        $db->setAccessible(true);
        $this->assertSame(self::DB, $db->getValue($page));

        $ar = $ref->getProperty('activeRecord');
        $ar->setAccessible(true);
        $this->assertSame(Cliente::class, $ar->getValue($page));

        $ff = $ref->getProperty('filterFields');
        $ff->setAccessible(true);
        $this->assertSame(['nome', 'cidade'], $ff->getValue($page));
    }

    public function testOnShowDelegatesToOnReload(): void
    {
        $page = $this->makePage();
        $this->seed('Ana');

        ob_start();
        $page->onShow([]);
        ob_end_clean();

        $this->assertStringContainsString('Ana', $page->getContents());
    }

    public function testOnReloadFilters(): void
    {
        $page = $this->makePage();
        $this->seed('Ana', 'Sintra');
        $this->seed('Bruno', 'POA');

        ob_start();
        $page->onReload(['nome' => 'Bruno']);
        ob_end_clean();

        $html = $page->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
    }

    public function testOnDeleteWithConfirmDeletesAndReloads(): void
    {
        $page = $this->makePage();
        $id = $this->seed('Ana');

        ob_start();
        $page->onDelete(['key' => $id, 'confirm' => 1]);
        ob_end_clean();

        TTransaction::open(self::DB);
        $total = (new TRepository(Cliente::class))->count();
        TTransaction::close();
        $this->assertSame(0, $total);
    }

    public function testOnExportCsvOutputsBomAndRows(): void
    {
        $page = $this->makePage();
        $this->seed('Ana', 'Sintra');

        ob_start();
        $page->onExportCSV([]);
        $out = (string) ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $out);
        $this->assertStringContainsString('Nome', $out);
        $this->assertStringContainsString('Ana', $out);
        $this->assertStringContainsString('Sintra', $out);
    }

    public function testOnExportCsvDoesNotTerminateScript(): void
    {
        $this->assertTrue(method_exists(TStandardList::class, 'onExportCSV'));
        $page = $this->makePage();
        ob_start();
        $page->onExportCSV([]);
        ob_end_clean();
        // chegou aqui => o método não fez exit
        $this->assertTrue(true);
    }

    public function testAbstractSignatures(): void
    {
        $ref = new \ReflectionClass(TStandardList::class);
        $this->assertTrue($ref->isAbstract());
        foreach (['getDatabase', 'getActiveRecord', 'getFilterFields', 'buildFilter', 'buildGrid'] as $m) {
            $this->assertTrue($ref->getMethod($m)->isAbstract(), $m);
        }
        $this->assertSame(TForm::class, (string) $ref->getMethod('buildFilter')->getReturnType());
        $this->assertSame(TDataGrid::class, (string) $ref->getMethod('buildGrid')->getReturnType());
    }
}

