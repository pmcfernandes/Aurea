<?php
declare(strict_types=1);

namespace Tests\Unit\Dialog;

use Aurea\Core\TAction;
use Aurea\Dialog\TMessage;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Dialog\TMessage */
final class TMessageTest extends TestCase
{
    public function testInfoType(): void
    {
        $msg = new TMessage('info', 'Olá');
        $html = $msg->getContents();
        $this->assertStringContainsString('alert-info', $html);
        $this->assertStringContainsString('Olá', $html);
        $this->assertStringContainsString('fa-circle-info', $html);
    }

    public function testSuccessType(): void
    {
        $msg = new TMessage('success', 'Gravado');
        $html = $msg->getContents();
        $this->assertStringContainsString('alert-success', $html);
        $this->assertStringContainsString('fa-circle-check', $html);
    }

    public function testWarningType(): void
    {
        $msg = new TMessage('warning', 'Atenção');
        $html = $msg->getContents();
        $this->assertStringContainsString('alert-warning', $html);
        $this->assertStringContainsString('fa-triangle-exclamation', $html);
    }

    public function testErrorTypeMapsToDanger(): void
    {
        $msg = new TMessage('error', 'Falhou');
        $html = $msg->getContents();
        $this->assertStringContainsString('alert-danger', $html);
        $this->assertStringContainsString('fa-circle-exclamation', $html);
    }

    public function testPosAction(): void
    {
        $action = new TAction(['ClienteList', 'onReload']);
        $msg = new TMessage('info', 'Texto', $action);
        $html = $msg->getContents();
        $this->assertStringContainsString('OK', $html);
        $this->assertStringContainsString(htmlspecialchars($action->toHref(), ENT_QUOTES), $html);
    }

    public function testWithoutPosActionHasNoOkButton(): void
    {
        $msg = new TMessage('info', 'Texto');
        $this->assertStringNotContainsString('>OK<', $msg->getContents());
    }

    public function testEscapesText(): void
    {
        $msg = new TMessage('info', '<b>X</b>');
        $html = $msg->getContents();
        $this->assertStringContainsString('&lt;b&gt;X&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>X</b>', $html);
    }
}
