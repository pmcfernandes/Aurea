<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use App\Model\Cliente;
use Aurea\Base\TStandardForm;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardFormTrait */
final class TStandardFormTraitTest extends TestCase
{
    public const DB = 'testbase_formtrait';

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

    private function makePage(): TStandardForm
    {
        return new class extends TStandardForm {
            protected function getDatabase(): string
            {
                return TStandardFormTraitTest::DB;
            }

            protected function getActiveRecord(): string
            {
                return Cliente::class;
            }

            protected function buildForm(): TFormBuilder
            {
                $form = new TFormBuilder('form_cliente');
                $nome = new TEntry('nome');
                $email = new TEntry('email');
                $form->addFields([new TLabel('Nome')], [$nome]);
                $form->addFields([new TLabel('E-mail')], [$email]);
                return $form;
            }
        };
    }

    private function fieldValue(TStandardForm $page, string $field): mixed
    {
        $prop = new \ReflectionProperty(TStandardForm::class, 'form');
        $prop->setAccessible(true);
        /** @var TFormBuilder $form */
        $form = $prop->getValue($page);
        return $form->getForm()->getField($field)?->getValue();
    }

    public function testOnSaveStoresRecord(): void
    {
        $page = $this->makePage();
        $_POST = ['nome' => 'Ana', 'email' => 'ana@example.com'];

        ob_start();
        $page->onSave([]);
        ob_end_clean();

        TTransaction::open(self::DB);
        $rows = (new TRepository(Cliente::class))->load();
        TTransaction::close();

        $this->assertCount(1, $rows);
        $this->assertSame('Ana', $rows[0]->nome);
        $this->assertSame('ana@example.com', $rows[0]->email);
    }

    public function testOnEditLoadsRecordIntoForm(): void
    {
        TTransaction::open(self::DB);
        $obj = new Cliente(['nome' => 'Bruno', 'email' => 'bruno@example.com']);
        $obj->store();
        $id = (int) $obj->id;
        TTransaction::close();

        $page = $this->makePage();
        ob_start();
        $page->onEdit(['key' => $id]);
        ob_end_clean();

        $this->assertSame('Bruno', $this->fieldValue($page, 'nome'));
        $this->assertSame('bruno@example.com', $this->fieldValue($page, 'email'));
    }

    public function testOnEditWithoutKeyClearsForm(): void
    {
        $page = $this->makePage();
        $_POST = ['nome' => 'X', 'email' => 'x@x.com'];
        ob_start();
        $page->onSave([]);
        ob_end_clean();
        ob_start();
        $page->onEdit([]);
        ob_end_clean();

        $this->assertNull($this->fieldValue($page, 'nome'));
    }

    public function testOnClearEmptiesForm(): void
    {
        $page = $this->makePage();
        $_POST = ['nome' => 'Ana', 'email' => 'ana@example.com'];
        ob_start();
        $page->onSave([]);
        ob_end_clean();

        $page->onClear([]);
        $this->assertNull($this->fieldValue($page, 'nome'));
        $this->assertNull($this->fieldValue($page, 'email'));
    }

    public function testOnSaveShowsSuccessMessage(): void
    {
        $page = $this->makePage();
        $_POST = ['nome' => 'Ana', 'email' => 'ana@example.com'];

        ob_start();
        $page->onSave([]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('Registro salvo', $out);
    }
}

