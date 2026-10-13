<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TVBox;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TVBox */
final class TVBoxTest extends TestCase
{
    public function testHasVerticalLayout(): void
    {
        $box = new TVBox;
        $html = $box->getContents();
        $this->assertStringContainsString('d-flex flex-column', $html);
        $this->assertStringContainsString('width:100%', $html);
        $this->assertStringStartsWith('<div', $html);
    }

    public function testAddChildren(): void
    {
        $box = new TVBox;
        $box->add('hello');
        $inner = new TComponent('span');
        $inner->add('world');
        $box->add($inner);
        $html = $box->getContents();
        $this->assertStringContainsString('hello', $html);
        $this->assertStringContainsString('<span>world</span>', $html);
    }
}
