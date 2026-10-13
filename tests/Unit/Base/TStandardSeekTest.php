<?php
declare(strict_types=1);

namespace Tests\Unit\Base;

use App\Model\Cliente;
use Aurea\Base\TStandardSeek;
use Aurea\Database\TTransaction;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Base\TStandardSeek */
final class TStandardSeekTest extends TestCase
{
    public const DB = 'testbase_standardseek';

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

    private function makePage(): TStandardSeek
    {
        return new class extends TStandardSeek {
            protected function getDatabase(): string
            {
                return TStandardSeekTest::DB;
            }

            protected function getActiveRecord(): string
            {
                return Cliente::class;
            }

            protected function getSearchFields(): array
            {
                return ['nome', 'cidade'];
            }

            protected function getKeyField(): string
            {
                return 'id';
            }

            protected function getDisplayField(): string
            {
                return 'nome';
            }
        };
    }

    private function seed(string $nome, string $cidade = 'Sintra'): void
    {
        TTransaction::open(self::DB);
        $obj = new Cliente(['nome' => $nome, 'email' => 'x@example.com', 'telefone' => '', 'cidade' => $cidade]);
        $obj->store();
        TTransaction::close();
    }

    public function testSetReturnFieldAndLabelAreFluent(): void
    {
        $page = $this->makePage();
        $this->assertSame($page, $page->setReturnField('cliente_id'));
        $this->assertSame($page, $page->setReturnLabel('cliente_nome'));
    }

    public function testOnSearchFillsGrid(): void
    {
        $page = $this->makePage();
        $this->seed('Ana');
        $this->seed('Bruno', 'POA');

        ob_start();
        $page->onSearch([]);
        ob_end_clean();

        $html = $page->getContents();
        $this->assertStringContainsString('Ana', $html);
        $this->assertStringContainsString('Bruno', $html);
    }

    public function testOnSearchWithFilter(): void
    {
        $page = $this->makePage();
        $this->seed('Ana', 'Sintra');
        $this->seed('Bruno', 'POA');

        ob_start();
        $page->onSearch(['nome' => 'Bruno']);
        ob_end_clean();

        $html = $page->getContents();
        $this->assertStringContainsString('Bruno', $html);
        $this->assertStringNotContainsString('Ana', $html);
    }

    public function testOnShowDelegatesToOnSearch(): void
    {
        $page = $this->makePage();
        $this->seed('Ana');

        ob_start();
        $page->onShow([]);
        ob_end_clean();

        $this->assertStringContainsString('Ana', $page->getContents());
    }

    public function testGetContentsIncludesSeekScriptAfterSearch(): void
    {
        $page = $this->makePage();
        $this->seed('Ana');

        $this->assertStringNotContainsString('Selecionar', $page->getContents());

        ob_start();
        $page->onSearch([]);
        ob_end_clean();

        $html = $page->getContents();
        $this->assertStringContainsString('<script>', $html);
        $this->assertStringContainsString('Selecionar', $html);
        $this->assertStringContainsString('__aureaSeekReturn', $html);
    }

    public function testAbstractSignatures(): void
    {
        $ref = new \ReflectionClass(TStandardSeek::class);
        $this->assertTrue($ref->isAbstract());
        foreach (['getDatabase', 'getActiveRecord', 'getSearchFields', 'getKeyField', 'getDisplayField'] as $m) {
            $this->assertTrue($ref->getMethod($m)->isAbstract(), $m);
        }
    }
}

