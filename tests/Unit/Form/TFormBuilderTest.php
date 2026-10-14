<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TSession;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TFormBuilder */
final class TFormBuilderTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->sessionBackup = $_SESSION ?? [];
        TSession::free();
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_SESSION = $this->sessionBackup;
        TSession::free();
    }

    public function testGetForm(): void
    {
        $b = new TFormBuilder('fb');
        $this->assertInstanceOf(TForm::class, $b->getForm());
        $this->assertSame('fb', $b->getForm()->getFormName());
    }

    public function testSetFormTitleGetContents(): void
    {
        $b = new TFormBuilder('fb');
        $this->assertSame($b, $b->setFormTitle('Cliente'));
        $this->assertStringContainsString('Cliente', $b->getContents());
    }

    public function testAddFields(): void
    {
        $b = new TFormBuilder('fb');
        $field = new TEntry('nome');
        $this->assertSame($b, $b->addFields([new TLabel('Nome')], [$field]));
        $this->assertSame($field, $b->getForm()->getField('nome'));
        $html = $b->getContents();
        $this->assertStringContainsString('name="nome"', $html);
        $this->assertStringContainsString('Nome', $html);
    }

    public function testAddFieldsWithSizes(): void
    {
        $b = new TFormBuilder('fb');
        $b->addFields(['Nome'], [new TEntry('nome')], [6]);
        $this->assertStringContainsString('name="nome"', $b->getContents());
    }

    public function testAddContent(): void
    {
        $b = new TFormBuilder('fb');
        $this->assertSame($b, $b->addContent('texto-extra'));
        $this->assertSame($b, $b->addContent(new TComponent('span')));
        $this->assertStringContainsString('texto-extra', $b->getContents());
    }

    public function testAddAction(): void
    {
        $b = new TFormBuilder('fb');
        $btn = $b->addAction('Salvar', new TAction(['Cli', 'onSave']), 'fa-save');
        $this->assertInstanceOf(TComponent::class, $btn);
        $html = $b->getContents();
        $this->assertStringContainsString('Salvar', $html);
        $this->assertStringContainsString('index.php?class=Cli', $html);
    }

    public function testAddActionLink(): void
    {
        $b = new TFormBuilder('fb');
        $link = $b->addActionLink('Voltar', new TAction(['Cli', 'onBack']));
        $this->assertInstanceOf(TComponent::class, $link);
        $this->assertStringContainsString('Voltar', $b->getContents());
    }

    public function testSetColumnClasses(): void
    {
        $b = new TFormBuilder('fb');
        $this->assertSame($b, $b->setColumnClasses(['col-md-6']));
        $b->addFields(['Nome'], [new TEntry('nome')]);
        $this->assertStringContainsString('col-md-6', $b->getContents());
    }

    public function testSetClientValidation(): void
    {
        $b = new TFormBuilder('fb');
        $this->assertSame($b, $b->setClientValidation(true));
        $html = $b->getContents();
        $this->assertStringContainsString('aurea-client-validation', $html);
        $this->assertStringContainsString('checkValidity', $html);
    }

    public function testAddHeaderActionLink(): void
    {
        $b = new TFormBuilder('fb');
        $b->setFormTitle('Cliente');
        $link = $b->addHeaderActionLink('Novo', new TAction(['Cli', 'onNew']));
        $this->assertInstanceOf(TComponent::class, $link);
        $html = $b->getContents();
        $this->assertStringContainsString('Novo', $html);
        $this->assertStringContainsString('card-header', $html);
    }

    public function testAddsSectionsAndHeaderFooterWidgets(): void
    {
        $b = new TFormBuilder('fb');
        $b->addSection('Dados pessoais', 'Preencha os dados.');
        $b->addHeaderWidget('<span>Estado</span>');
        $b->addFooterWidget('<small>Obrigatório</small>');
        $html = $b->getContents();
        $this->assertStringContainsString('Dados pessoais', $html);
        $this->assertStringContainsString('Preencha os dados.', $html);
        $this->assertStringContainsString('Estado', $html);
        $this->assertStringContainsString('Obrigatório', $html);
    }

    public function testSetDataGetData(): void
    {
        $b = new TFormBuilder('fb');
        $b->addFields(['Nome'], [new TEntry('nome')]);
        $this->assertSame($b, $b->setData(['nome' => 'Ana']));
        $this->assertSame('Ana', $b->getForm()->getField('nome')->getValue());
        $_POST['nome'] = 'Bruno';
        $data = $b->getData();
        $this->assertSame('Bruno', $data->nome);
    }

    public function testValidate(): void
    {
        $b = new TFormBuilder('fb');
        $b->enableCsrf(false);
        $b->addFields(['Nome'], [(new TEntry('nome'))->addValidation('Nome', new TRequiredValidator())]);
        $b->setData(['nome' => 'Ana']);
        $b->validate();
        $this->assertTrue(true);
    }

    public function testValidateFails(): void
    {
        $b = new TFormBuilder('fb');
        $b->enableCsrf(false);
        $b->addFields(['Nome'], [(new TEntry('nome'))->addValidation('Nome', new TRequiredValidator())]);
        $this->expectException(\Exception::class);
        $b->validate();
    }

    public function testCsrfEnabledByDefault(): void
    {
        $b = new TFormBuilder('fb_csrf');
        $b->addFields(['Nome'], [new TEntry('nome')]);
        $this->assertStringContainsString('_csrf', $b->getContents());
    }

    public function testClear(): void
    {
        $b = new TFormBuilder('fb');
        $b->addFields(['Nome'], [new TEntry('nome')]);
        $b->setData(['nome' => 'Ana']);
        $this->assertSame($b, $b->clear());
        $this->assertNull($b->getForm()->getField('nome')->getValue());
    }
}
