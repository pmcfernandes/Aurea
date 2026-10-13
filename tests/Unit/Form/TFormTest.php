<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Core\TCsrf;
use Aurea\Core\TComponent;
use Aurea\Core\TSession;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TForm */
final class TFormTest extends TestCase
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

    public function testAddFieldGetFieldsGetField(): void
    {
        $form = new TForm('cli');
        $this->assertSame('cli', $form->getFormName());
        $field = new TEntry('nome');
        $this->assertSame($form, $form->addField($field));
        $this->assertSame(['nome' => $field], $form->getFields());
        $this->assertSame($field, $form->getField('nome'));
        $this->assertNull($form->getField('inexistente'));
    }

    public function testAddAction(): void
    {
        $form = new TForm('cli');
        $btn = $form->addAction('Salvar', new TAction(['Cli', 'onSave']), 'fa-save');
        $this->assertInstanceOf(TComponent::class, $btn);
        $this->assertStringContainsString('index.php?class=Cli', (string) $btn->getProperty('formaction'));
    }

    public function testSetData(): void
    {
        $form = new TForm('cli');
        $field = new TEntry('nome');
        $form->addField($field);
        $form->setData(['nome' => 'Ana']);
        $this->assertSame('Ana', $field->getValue());
        $form->setData((object) ['nome' => 'Bruno']);
        $this->assertSame('Bruno', $field->getValue());
    }

    public function testGetDataFromPost(): void
    {
        $form = new TForm('cli');
        $form->addField(new TEntry('nome'));
        $_POST['nome'] = 'Ana';
        $data = $form->getData();
        $this->assertSame('Ana', $data->nome);
        $this->assertSame('Ana', $form->getField('nome')->getValue());
    }

    public function testGetDataWithClass(): void
    {
        $form = new TForm('cli');
        $form->addField(new TEntry('nome'));
        $_POST['nome'] = 'Ana';
        $data = $form->getData(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertSame('Ana', $data->nome);
    }

    public function testValidatePasses(): void
    {
        $form = new TForm('cli');
        $field = (new TEntry('nome'))->addValidation('Nome', new TRequiredValidator());
        $form->addField($field);
        $form->setData(['nome' => 'Ana']);
        $form->validate();
        $this->assertTrue(true);
    }

    public function testValidateFails(): void
    {
        $form = new TForm('cli');
        $form->addField((new TEntry('nome'))->addValidation('Nome', new TRequiredValidator()));
        $this->expectException(\Exception::class);
        $form->validate();
    }

    public function testEnableCsrfGetContentsContainsToken(): void
    {
        $form = new TForm('cli');
        $this->assertStringNotContainsString('_csrf', $form->getContents());
        $this->assertSame($form, $form->enableCsrf());
        $html = $form->getContents();
        $this->assertStringContainsString('name="_csrf"', $html);
        $this->assertStringContainsString(TCsrf::token(), $html);
    }

    public function testValidateWithValidCsrf(): void
    {
        $form = new TForm('cli');
        $form->enableCsrf();
        $_POST[TCsrf::FIELD] = TCsrf::token();
        $form->validate();
        $this->assertTrue(true);
    }

    public function testValidateWithInvalidCsrfThrows(): void
    {
        $form = new TForm('cli');
        $form->enableCsrf();
        $_POST[TCsrf::FIELD] = 'token-invalido';
        $this->expectException(\Exception::class);
        $form->validate();
    }

    public function testClear(): void
    {
        $form = new TForm('cli');
        $field = new TEntry('nome');
        $form->addField($field);
        $form->setData(['nome' => 'Ana']);
        $this->assertSame($form, $form->clear());
        $this->assertNull($field->getValue());
    }
}
