<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc;

use Aurea\Http\THttpResponse;
use Aurea\Mvc\TController;
use Aurea\Mvc\TView;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Mvc\Fixtures\BlockerController;
use Tests\Unit\Mvc\Fixtures\HomeController;

/** @covers \Aurea\Mvc\TController */
final class TControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        HomeController::$calls = [];
    }

    public function testCallDispatchesOnMethodWithFilters(): void
    {
        $c = new HomeController(['a' => 1]);
        $this->assertSame('home-html', $c->index());
        $this->assertSame(['before', 'index', 'after'], HomeController::$calls);
    }

    public function testCallUnknownThrows(): void
    {
        $c = new HomeController;
        $this->expectException(\RuntimeException::class);
        $c->missing();
    }

    public function testBeforeFalseCancels(): void
    {
        $c = new BlockerController;
        $this->assertNull($c->index());
    }

    public function testViewHelper(): void
    {
        $c = new HomeController;
        $view = new \ReflectionMethod($c, 'view');
        $view->setAccessible(true);
        $result = $view->invoke($c, 'home/index', ['a' => 1]);
        $this->assertInstanceOf(TView::class, $result);
    }

    public function testJsonHelper(): void
    {
        $c = new HomeController;
        $m = new \ReflectionMethod($c, 'json');
        $m->setAccessible(true);
        $res = $m->invoke($c, ['ok' => true], 201);
        $this->assertInstanceOf(THttpResponse::class, $res);
        $this->assertSame(201, $res->getStatus());
        $this->assertSame('{"ok":true}', $res->getBody());
    }

    public function testRedirectHelper(): void
    {
        $c = new HomeController;
        $m = new \ReflectionMethod($c, 'redirect');
        $m->setAccessible(true);
        $res = $m->invoke($c, '/x', 301);
        $this->assertSame(301, $res->getStatus());
        $this->assertSame('/x', $res->getHeaders()['Location']);
    }

    public function testBaseBeforeAfterDefaults(): void
    {
        $c = new class extends TController {
        };
        $before = new \ReflectionMethod($c, 'before');
        $before->setAccessible(true);
        $after = new \ReflectionMethod($c, 'after');
        $after->setAccessible(true);
        $this->assertNull($before->invoke($c));
        $after->invoke($c);
        $this->assertTrue(true);
    }
}
