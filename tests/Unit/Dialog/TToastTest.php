<?php
declare(strict_types=1);

namespace Tests\Unit\Dialog;

use Aurea\Dialog\TToast;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Dialog\TToast */
final class TToastTest extends TestCase
{
    public function testInfoType(): void
    {
        $toast = new TToast('info', 'Olá');
        $html = $toast->getContents();
        $this->assertStringContainsString('text-bg-primary', $html);
        $this->assertStringContainsString('Olá', $html);
    }

    public function testSuccessType(): void
    {
        $toast = new TToast('success', 'Ok');
        $this->assertStringContainsString('text-bg-success', $toast->getContents());
    }

    public function testWarningType(): void
    {
        $toast = new TToast('warning', 'Cuidado');
        $this->assertStringContainsString('text-bg-warning', $toast->getContents());
    }

    public function testErrorTypeMapsToDanger(): void
    {
        $toast = new TToast('error', 'Falhou');
        $this->assertStringContainsString('text-bg-danger', $toast->getContents());
    }

    public function testEscapesText(): void
    {
        $toast = new TToast('info', '<b>X</b>');
        $html = $toast->getContents();
        $this->assertStringContainsString('&lt;b&gt;X&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>X</b>', $html);
    }
}
