<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TPage;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TPage */
final class TPageTest extends TestCase
{
    public function testDefaults(): void
    {
        $p = new TPage;
        $this->assertSame('#aurea-content', $p->getTargetContainer());
        $this->assertStringContainsString('aurea-page', $p->getContents());
    }

    public function testTargetContainer(): void
    {
        $p = new TPage;
        $p->setTargetContainer('#x');
        $this->assertSame('#x', $p->getTargetContainer());
    }

    public function testOnShowDoesNothing(): void
    {
        $p = new TPage;
        $p->onShow(['a' => 1]);
        $this->assertTrue(true);
    }
}
