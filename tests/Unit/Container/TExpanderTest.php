<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TExpander;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TExpander */
final class TExpanderTest extends TestCase
{
    public function testSetOpenTrue(): void
    {
        $exp = new TExpander('Secção');
        $this->assertSame($exp, $exp->setOpen(true));
        $html = $exp->getContents();
        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('open="open"', $html);
    }

    public function testSetOpenFalse(): void
    {
        $exp = new TExpander('Secção');
        $exp->setOpen(true)->setOpen(false);
        $html = $exp->getContents();
        $this->assertStringContainsString('<details', $html);
        $this->assertStringNotContainsString('open=', $html);
    }

    public function testRendersTitleAndChildren(): void
    {
        $exp = new TExpander('<Detalhes>');
        $exp->add('miúdo');
        $html = $exp->getContents();
        $this->assertStringContainsString('<summary>&lt;Detalhes&gt;</summary>', $html);
        $this->assertStringContainsString('miúdo', $html);
    }
}
