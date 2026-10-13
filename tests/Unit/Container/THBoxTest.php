<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\THBox;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\THBox */
final class THBoxTest extends TestCase
{
    public function testHasHorizontalLayout(): void
    {
        $box = new THBox;
        $html = $box->getContents();
        $this->assertStringContainsString('d-flex flex-row', $html);
        $this->assertStringContainsString('align-items-center', $html);
        $this->assertStringStartsWith('<div', $html);
    }

    public function testAddChildren(): void
    {
        $box = new THBox;
        $box->add('left');
        $inner = new TComponent('span');
        $inner->add('right');
        $box->add($inner);
        $html = $box->getContents();
        $this->assertStringContainsString('left', $html);
        $this->assertStringContainsString('<span>right</span>', $html);
    }
}
