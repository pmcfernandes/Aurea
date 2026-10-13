<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use Aurea\Core\TAction;
use Aurea\Grid\TPageNavigation;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TPageNavigation */
final class TPageNavigationTest extends TestCase
{
    public function testSinglePageRendersEmpty(): void
    {
        $this->assertSame('', (new TPageNavigation(1, 1))->getContents());
    }

    public function testZeroPagesRendersEmpty(): void
    {
        $this->assertSame('', (new TPageNavigation(1, 0))->getContents());
    }

    public function testMultiplePagesRenderLinks(): void
    {
        $html = (new TPageNavigation(1, 3))->getContents();
        $this->assertStringContainsString('pagination', $html);
        $this->assertSame(3, substr_count($html, 'page-item'));
        $this->assertStringContainsString('?page=1', $html);
        $this->assertStringContainsString('?page=3', $html);
    }

    public function testCurrentPageIsActive(): void
    {
        $html = (new TPageNavigation(2, 3))->getContents();
        $this->assertStringContainsString("page-item active", $html);
        $this->assertSame(1, substr_count($html, 'active'));
    }

    public function testLinksWithAction(): void
    {
        $html = (new TPageNavigation(1, 2, new TAction(['FakeList', 'onReload'])))->getContents();
        $this->assertStringContainsString('class=FakeList', $html);
        $this->assertStringContainsString('page=1', $html);
        $this->assertStringContainsString('page=2', $html);
    }
}
