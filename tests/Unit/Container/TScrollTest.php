<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TScroll;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TScroll */
final class TScrollTest extends TestCase
{
    public function testDefaultHeight(): void
    {
        $scroll = new TScroll;
        $this->assertStringContainsString('height:300px', $scroll->getContents());
    }

    public function testSetHeight(): void
    {
        $scroll = new TScroll;
        $this->assertSame($scroll, $scroll->setHeight(200));
        $html = $scroll->getContents();
        $this->assertStringContainsString('height:200px', $html);
        $this->assertStringContainsString('overflow:auto', $html);
    }

    public function testConstructorHeight(): void
    {
        $scroll = new TScroll(150);
        $this->assertStringContainsString('height:150px', $scroll->getContents());
    }
}
