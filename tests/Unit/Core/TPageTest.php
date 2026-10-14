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

    public function testLifecycleHooksCanBeOverridden(): void
    {
        $page = new class extends TPage {
            public array $events = [];
            public function onBeforeAction(string $method, array $param = []): void { $this->events[] = ['before', $method, $param]; }
            public function onAfterAction(string $method, array $param = [], mixed $result = null): void { $this->events[] = ['after', $method, $result]; }
        };
        $page->onBeforeAction('onShow', ['a' => 1]);
        $page->onAfterAction('onShow', ['a' => 1], 'ok');
        $this->assertSame('before', $page->events[0][0]);
        $this->assertSame('ok', $page->events[1][2]);
    }
}
