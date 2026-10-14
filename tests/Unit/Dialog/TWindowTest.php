<?php
declare(strict_types=1);

namespace Tests\Unit\Dialog;

use Aurea\Core\TAction;
use Aurea\Core\TScript;
use Aurea\Dialog\TWindow;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Dialog\TWindow */
final class TWindowTest extends TestCase
{
    protected function tearDown(): void
    {
        TScript::clear();
    }

    public function testGetModalId(): void
    {
        $win = new TWindow('Detalhes');
        $this->assertStringStartsWith('aurea-modal-', $win->getModalId());
    }

    public function testGetContentsRendersModal(): void
    {
        $win = new TWindow('Detalhes', 600);
        $html = $win->getContents();
        $this->assertStringContainsString('modal-dialog', $html);
        $this->assertStringContainsString('max-width:600px', $html);
        $this->assertStringContainsString('Detalhes', $html);
        $this->assertStringContainsString($win->getModalId(), $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString($win->getTitleId(), $html);
    }

    public function testSetContent(): void
    {
        $win = new TWindow('T');
        $this->assertSame($win, $win->setContent('corpo aqui'));
        $this->assertStringContainsString('corpo aqui', $win->getContents());
    }

    public function testAddButtonWithAction(): void
    {
        $win = new TWindow('T');
        $action = new TAction(['ClienteList', 'onReload']);
        $this->assertSame($win, $win->addButton('Ir', $action, 'btn-primary'));
        $html = $win->getContents();
        $this->assertStringContainsString('Ir', $html);
        $this->assertStringContainsString(htmlspecialchars($action->toHref(), ENT_QUOTES), $html);
        $this->assertStringContainsString('<a ', $html);
    }

    public function testAddButtonWithoutActionDismisses(): void
    {
        $win = new TWindow('T');
        $win->addButton('Fechar');
        $html = $win->getContents();
        $this->assertStringContainsString('Fechar', $html);
        $this->assertStringContainsString('data-bs-dismiss="modal"', $html);
    }

    public function testShowOutputsModalAndScript(): void
    {
        TScript::clear();
        $win = new TWindow('Detalhes', 600);
        $win->setContent('corpo');
        ob_start();
        $win->show();
        $out = (string) ob_get_clean();
        TScript::clear();
        $this->assertStringContainsString($win->getModalId(), $out);
        $this->assertStringContainsString('corpo', $out);
        $this->assertStringContainsString('bootstrap.Modal', $out);
        $this->assertStringContainsString('shown.bs.modal', $out);
    }
}
