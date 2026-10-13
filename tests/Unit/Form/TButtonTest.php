<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TAction;
use Aurea\Form\TButton;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TButton */
final class TButtonTest extends TestCase
{
    public function testRendersSubmitButton(): void
    {
        $b = new TButton('Salvar');
        $html = $b->getContents();
        $this->assertStringContainsString('<button', $html);
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('Salvar', $html);
        $this->assertStringContainsString('btn btn-primary', $html);
    }

    public function testSetAction(): void
    {
        $b = new TButton('Recarregar');
        $action = new TAction(['ClienteList', 'onReload']);
        $this->assertSame($b, $b->setAction($action));
        $html = $b->getContents();
        $this->assertStringContainsString('type="button"', $html);
        $this->assertStringContainsString('onclick=', $html);
        $this->assertStringContainsString('ClienteList', $html);
        $this->assertStringContainsString('onReload', $html);
    }

    public function testSetActionWithConfirm(): void
    {
        $b = new TButton('Excluir');
        $b->setAction(new TAction(['ClienteList', 'onDelete']), 'Confirma?');
        $html = $b->getContents();
        $this->assertStringContainsString('type="button"', $html);
        $this->assertStringContainsString('Confirma?', $html);
    }
}
