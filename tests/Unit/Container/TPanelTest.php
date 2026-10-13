<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TPanel;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TPanel */
final class TPanelTest extends TestCase
{
    public function testTitleRendersHeader(): void
    {
        $panel = new TPanel('Meu Título');
        $html = $panel->getContents();
        $this->assertStringContainsString('card-header', $html);
        $this->assertStringContainsString('Meu Título', $html);
        $this->assertStringContainsString('card-body', $html);
    }

    public function testNoTitleRendersNoHeader(): void
    {
        $panel = new TPanel;
        $this->assertStringNotContainsString('card-header', $panel->getContents());
    }

    public function testAddGoesToBody(): void
    {
        $panel = new TPanel('T');
        $this->assertSame($panel, $panel->add('conteúdo'));
        $html = $panel->getContents();
        $this->assertStringContainsString('card-body', $html);
        $this->assertStringContainsString('conteúdo', $html);
    }

    public function testAddToBody(): void
    {
        $panel = new TPanel('T');
        $inner = new TComponent('span');
        $inner->add('interno');
        $this->assertSame($panel, $panel->addToBody($inner));
        $this->assertStringContainsString('<span>interno</span>', $panel->getContents());
    }
}
