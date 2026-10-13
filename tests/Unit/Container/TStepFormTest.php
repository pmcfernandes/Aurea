<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TStepForm;
use Aurea\Core\TAction;
use Aurea\Form\TEntry;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TStepForm */
final class TStepFormTest extends TestCase
{
    /** @var array */
    private array $postBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
    }

    public function testAddStepAndRender(): void
    {
        $wizard = new TStepForm('cadastro');
        $nome = new TEntry('nome');
        $nome->setLabel('Nome');
        $cidade = new TEntry('cidade');
        $cidade->setLabel('Cidade');
        $this->assertSame($wizard, $wizard->addStep('Dados', [$nome]));
        $wizard->addStep('Morada', [$cidade]);
        $html = $wizard->getContents();
        $this->assertStringContainsString('Dados', $html);
        $this->assertStringContainsString('Morada', $html);
        $this->assertStringContainsString('Nome', $html);
        $this->assertStringContainsString('Cidade', $html);
        $this->assertStringContainsString('Seguinte', $html);
        $this->assertStringContainsString('Anterior', $html);
        $this->assertStringContainsString('Gravar', $html);
    }

    public function testSetSaveAction(): void
    {
        $wizard = new TStepForm('cadastro');
        $wizard->addStep('Só', [new TEntry('nome')]);
        $action = new TAction(['ClienteForm', 'onSave']);
        $this->assertSame($wizard, $wizard->setSaveAction($action));
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $wizard->getContents());
    }

    public function testGetDataFromPost(): void
    {
        $wizard = new TStepForm('cadastro');
        $wizard->addStep('Dados', [new TEntry('nome'), new TEntry('email')]);
        $_POST = ['nome' => 'Ana', 'email' => 'ana@exemplo.pt', 'outro' => 'ignorado'];
        $data = $wizard->getData();
        $this->assertSame('Ana', $data->nome);
        $this->assertSame('ana@exemplo.pt', $data->email);
        $this->assertObjectNotHasProperty('outro', $data);
    }

    public function testGetDataMissingFieldIsNull(): void
    {
        $wizard = new TStepForm('cadastro');
        $wizard->addStep('Dados', [new TEntry('nome')]);
        $_POST = [];
        $data = $wizard->getData();
        $this->assertNull($data->nome);
    }
}
