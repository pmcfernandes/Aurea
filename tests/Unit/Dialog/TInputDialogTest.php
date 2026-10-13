<?php
declare(strict_types=1);

namespace Tests\Unit\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TScript;
use Aurea\Dialog\TInputDialog;
use Aurea\Form\TEntry;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Dialog\TInputDialog */
final class TInputDialogTest extends TestCase
{
    /** @var array */
    private array $postBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        TScript::clear();
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        TScript::clear();
    }

    public function testGetModalId(): void
    {
        $dlg = new TInputDialog('Editar');
        $this->assertStringStartsWith('inputdlg-', $dlg->getModalId());
    }

    public function testAddFieldAndGetContents(): void
    {
        $dlg = new TInputDialog('Editar');
        $field = new TEntry('nome');
        $field->setLabel('Nome');
        $this->assertSame($dlg, $dlg->addField($field));
        $html = $dlg->getContents();
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('Nome', $html);
        $this->assertStringContainsString('name="nome"', $html);
        $this->assertStringContainsString('Gravar', $html);
    }

    public function testSetSaveAction(): void
    {
        $dlg = new TInputDialog('Editar');
        $dlg->addField(new TEntry('nome'));
        $action = new TAction(['ClienteForm', 'onSave']);
        $this->assertSame($dlg, $dlg->setSaveAction($action));
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $dlg->getContents());
    }

    public function testSetCancelLabel(): void
    {
        $dlg = new TInputDialog('Editar');
        $this->assertSame($dlg, $dlg->setCancelLabel('Fechar'));
        $this->assertStringContainsString('Fechar', $dlg->getContents());
    }

    public function testGetData(): void
    {
        $dlg = new TInputDialog('Editar');
        $dlg->addField(new TEntry('nome'));
        $dlg->addField(new TEntry('email'));
        $_POST = ['nome' => 'Ana', 'email' => 'ana@exemplo.pt'];
        $data = $dlg->getData();
        $this->assertSame('Ana', $data->nome);
        $this->assertSame('ana@exemplo.pt', $data->email);
    }

    public function testShowOutputsModalAndScript(): void
    {
        $dlg = new TInputDialog('Editar');
        $dlg->addField(new TEntry('nome'));
        ob_start();
        $dlg->show();
        $out = (string) ob_get_clean();
        TScript::clear();
        $this->assertStringContainsString($dlg->getModalId(), $out);
        $this->assertStringContainsString('Editar', $out);
        $this->assertStringContainsString('bootstrap.Modal', $out);
    }
}
