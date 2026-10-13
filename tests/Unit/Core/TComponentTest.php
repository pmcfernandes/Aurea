<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TComponent */
final class TComponentTest extends TestCase
{
    public function testRenderElement(): void
    {
        $c = new TComponent('div');
        $c->setProperty('id', 'x');
        $c->add('a<b');
        $this->assertSame('<div id="x">a<b</div>', $c->getContents());
    }

    public function testNestedChildren(): void
    {
        $outer = new TComponent('div');
        $inner = new TComponent('span');
        $inner->add('oi');
        $outer->add($inner);
        $this->assertSame('<div><span>oi</span></div>', $outer->getContents());
    }

    public function testVoidTag(): void
    {
        $input = new TComponent('input');
        $input->setProperty('type', 'text');
        $this->assertSame('<input type="text">', $input->getContents());
    }

    public function testEscapesAttributes(): void
    {
        $c = new TComponent('div');
        $c->setProperty('title', 'a"b&c');
        $this->assertStringContainsString('title="a&quot;b&amp;c"', $c->getContents());
    }

    public function testMagicAccessors(): void
    {
        $c = new TComponent('div');
        $c->foo = 'bar';
        $this->assertSame('bar', $c->foo);
        $this->assertSame('bar', $c->getProperty('foo'));
        $c->setProperty('foo', null);
        $this->assertNull($c->getProperty('foo'));
    }

    public function testAddClassIdStyleChildren(): void
    {
        $c = new TComponent('div');
        $c->addClass('a')->addClass('b');
        $c->setId('myid')->setStyle('width:10px');
        $html = $c->getContents();
        $this->assertStringContainsString('class="a b"', $html);
        $this->assertStringContainsString('id="myid"', $html);
        $this->assertStringContainsString('style="width:10px"', $html);
        $c->setChildren([new TComponent('br')]);
        $this->assertSame('<div class="a b" id="myid" style="width:10px"><br></div>', $c->getContents());
    }

    public function testShowAndToString(): void
    {
        $c = new TComponent('p');
        $c->add('hi');
        $this->assertSame('<p>hi</p>', (string) $c);
        ob_start();
        $c->show();
        $this->assertSame('<p>hi</p>', (string) ob_get_clean());
    }

    public function testCloneIsDeep(): void
    {
        $c = new TComponent('div');
        $c->add(new TComponent('span'));
        $clone = clone $c;
        $this->assertSame($c->getContents(), $clone->getContents());
        $this->assertNotSame($c, $clone);
    }
}
