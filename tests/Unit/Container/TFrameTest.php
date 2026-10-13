<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TFrame;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TFrame */
final class TFrameTest extends TestCase
{
    public function testConstructorLegend(): void
    {
        $frame = new TFrame('Legenda');
        $html = $frame->getContents();
        $this->assertStringContainsString('<fieldset', $html);
        $this->assertStringContainsString('<legend>Legenda</legend>', $html);
    }

    public function testSetLegend(): void
    {
        $frame = new TFrame('Antiga');
        $this->assertSame($frame, $frame->setLegend('Nova'));
        $html = $frame->getContents();
        $this->assertStringContainsString('<legend>Nova</legend>', $html);
        $this->assertStringNotContainsString('Antiga', $html);
    }

    public function testAddGoesToBody(): void
    {
        $frame = new TFrame('L');
        $frame->add('texto');
        $inner = new TComponent('span');
        $inner->add('dentro');
        $frame->add($inner);
        $html = $frame->getContents();
        $this->assertStringContainsString('texto', $html);
        $this->assertStringContainsString('<span>dentro</span>', $html);
        $this->assertStringContainsString('<div>', $html);
    }
}
