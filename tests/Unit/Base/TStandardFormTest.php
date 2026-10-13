<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use App\Model\Cliente;
use Aurea\Base\TStandardForm;
use Aurea\Database\TTransaction;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardForm */
final class TStandardFormTest extends TestCase
{
    public const DB = 'testbase_standardform';

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
                return TStandardFormTest::DB;
            }

            protected function getActiveRecord(): string
            {
                return Cliente::class;
            }

            protected function buildForm(): TFormBuilder
            {
                $form = new TFormBuilder('form_cliente');
                $form->addFields([new TLabel('Nome')], [new TEntry('nome')]);
                return $form;
            }
        };
    }

    public function testConstructorBindsDatabaseAndRecord(): void
    {
        $page = $this->makePage();
        $ref = new \ReflectionClass($page);

        $db = $ref->getProperty('database');
        $db->setAccessible(true);
        $this->assertSame(self::DB, $db->getValue($page));

        $ar = $ref->getProperty('activeRecord');
        $ar->setAccessible(true);
        $this->assertSame(Cliente::class, $ar->getValue($page));

        $form = $ref->getProperty('form');
        $form->setAccessible(true);
        $this->assertInstanceOf(TFormBuilder::class, $form->getValue($page));
    }

    public function testFormIsAddedToPageContents(): void
    {
        $page = $this->makePage();
        $this->assertStringContainsString('form_cliente', $page->getContents());
    }

    public function testOnShowLoadsRecord(): void
    {
        TTransaction::open(self::DB);
        $obj = new Cliente(['nome' => 'Ana', 'email' => 'ana@example.com']);
        $obj->store();
        $id = (int) $obj->id;
        TTransaction::close();

        $page = $this->makePage();
        ob_start();
        $page->onShow(['key' => $id]);
        ob_end_clean();

        $prop = new \ReflectionProperty(TStandardForm::class, 'form');
        $prop->setAccessible(true);
        /** @var TFormBuilder $form */
        $form = $prop->getValue($page);
        $this->assertSame('Ana', $form->getForm()->getField('nome')?->getValue());
    }

    public function testAbstractSignatures(): void
    {
        $ref = new \ReflectionClass(TStandardForm::class);
        $this->assertTrue($ref->isAbstract());
        $this->assertTrue($ref->getMethod('getDatabase')->isAbstract());
        $this->assertTrue($ref->getMethod('getActiveRecord')->isAbstract());
        $this->assertTrue($ref->getMethod('buildForm')->isAbstract());
        $this->assertSame(TFormBuilder::class, (string) $ref->getMethod('buildForm')->getReturnType());
    }
}

