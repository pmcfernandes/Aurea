<?php
declare(strict_types=1);

namespace Tests\Unit\Dialog;

use Aurea\Core\TAction;
use Aurea\Dialog\TQuestion;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Dialog\TQuestion */
final class TQuestionTest extends TestCase
{
    public function testRendersYesAndDefaultNo(): void
    {
        $yes = new TAction(['ClienteList', 'onDelete'], ['key' => 5]);
        $q = new TQuestion('Excluir?', $yes);
        $html = $q->getContents();
        $this->assertStringContainsString('Excluir?', $html);
        $this->assertStringContainsString('Sim', $html);
        $this->assertStringContainsString('Não', $html);
        $this->assertStringContainsString(htmlspecialchars($yes->toHref(), ENT_QUOTES), $html);
        $this->assertStringContainsString('javascript:history.back()', $html);
    }

    public function testCustomNoAction(): void
    {
        $yes = new TAction(['ClienteList', 'onDelete']);
        $no = new TAction(['ClienteList', 'onReload']);
        $q = new TQuestion('Excluir?', $yes, $no);
        $html = $q->getContents();
        $this->assertStringContainsString(htmlspecialchars($no->toHref(), ENT_QUOTES), $html);
        $this->assertStringNotContainsString('history.back()', $html);
    }

    public function testEscapesMessage(): void
    {
        $q = new TQuestion('<b>Sair?</b>', new TAction(['X', 'onY']));
        $html = $q->getContents();
        $this->assertStringContainsString('&lt;b&gt;Sair?&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Sair?</b>', $html);
    }
}
