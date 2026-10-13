<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TComponent;
use Aurea\Core\TStyle;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TStyle */
final class TStyleTest extends TestCase
{
    public function testRenderApplyCreate(): void
    {
        $s = new TStyle(['color' => 'red']);
        $s->set('margin', '0');
        $this->assertSame('color: red; margin: 0;', $s->render());
        $c = new TComponent('div');
        $this->assertSame($c, $s->applyTo($c));
        $this->assertSame('color: red; margin: 0;', $c->getProperty('style'));
        $s2 = TStyle::create(['a' => 'b']);
        $this->assertSame('a: b;', $s2->render());
    }
}
