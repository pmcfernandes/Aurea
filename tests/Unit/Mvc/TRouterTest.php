<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc;

use Aurea\Mvc\TRouter;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Mvc\Fixtures\BlockerController;
use Tests\Unit\Mvc\Fixtures\HomeController;

/** @covers \Aurea\Mvc\TRouter */
final class TRouterTest extends TestCase
{
    private function router(): TRouter
    {
        $r = new TRouter('Tests\\Unit\\Mvc\\Fixtures\\');
        $r->add('', ['controller' => 'Home', 'action' => 'index']);
        $r->add('{controller}/{action}');
        $r->add('{controller}/{action}/{name}');
        $r->add('posts/{id:\d+}/{action}');
        return $r;
    }

    protected function tearDown(): void
    {
        HomeController::$calls = [];
        $_GET = [];
        unset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
    }

    public function testAddAndGetRoutes(): void
    {
        $r = new TRouter;
        $this->assertSame([], $r->getRoutes());
        $r->add('a', ['controller' => 'X']);
        $this->assertCount(1, $r->getRoutes());
    }

    public function testMatchExtractsParams(): void
    {
        $r = $this->router();
        $this->assertTrue($r->match('home/show'));
        $this->assertSame('home', $r->getParams()['controller']);
        $this->assertSame('show', $r->getParams()['action']);
        $this->assertTrue($r->match('posts/42/show'));
        $this->assertSame('42', $r->getParams()['id']);
        // rota genérica de 3 segmentos também casa aqui
        $this->assertTrue($r->match('posts/abc/show'));
        $this->assertSame('abc', $r->getParams()['action']);
        $this->assertFalse($r->match('a/b/c/d'));
    }

    public function testDispatchRunsActionWithFilters(): void
    {
        $r = $this->router();
        ob_start();
        $result = $r->dispatch('home/show/Ana');
        $out = (string) ob_get_clean();
        $this->assertSame('show:Ana', $result);
        $this->assertSame('show:Ana', $out);
        $this->assertSame(['before', 'after'], HomeController::$calls);
    }

    public function testDispatchDefaultRoute(): void
    {
        $r = $this->router();
        ob_start();
        $result = $r->dispatch('');
        ob_end_clean();
        $this->assertSame('home-html', $result);
        $this->assertSame(['before', 'index', 'after'], HomeController::$calls);
    }

    public function testDispatchSendsJsonResponse(): void
    {
        $r = new TRouter('Tests\\Unit\\Mvc\\Fixtures\\');
        $r->add('ping', ['controller' => 'Home', 'action' => 'ping']);
        ob_start();
        $result = $r->dispatch('ping');
        $out = (string) ob_get_clean();
        $this->assertInstanceOf(\Aurea\Http\THttpResponse::class, $result);
        $this->assertSame('{"pong":true}', $out);
    }

    public function testDispatchNoRouteThrows404(): void
    {
        $r = $this->router();
        try {
            $r->dispatch('a/b/c/d');
            $this->fail('devia lançar');
        } catch (\RuntimeException $e) {
            $this->assertSame(404, $e->getCode());
        }
    }

    public function testDispatchMissingControllerThrows(): void
    {
        $r = new TRouter('Tests\\Unit\\Mvc\\Fixtures\\');
        $r->add('x', ['controller' => 'Missing', 'action' => 'index']);
        $this->expectException(\RuntimeException::class);
        $r->dispatch('x');
    }

    public function testDispatchRejectsOnPrefix(): void
    {
        $r = new TRouter('Tests\\Unit\\Mvc\\Fixtures\\');
        $r->add('x', ['controller' => 'Home', 'action' => 'onIndex']);
        $this->expectException(\RuntimeException::class);
        $r->dispatch('x');
    }

    public function testDispatchStripsQueryString(): void
    {
        $r = $this->router();
        ob_start();
        $result = $r->dispatch('home/show/Bob&page=2');
        ob_end_clean();
        $this->assertSame('show:Bob', $result);
    }

    public function testBlockerCancelsAction(): void
    {
        $r = new TRouter('Tests\\Unit\\Mvc\\Fixtures\\');
        $r->add('b', ['controller' => 'Blocker', 'action' => 'index']);
        $this->assertNull($r->dispatch('b'));
    }

    public function testDispatchFromRequestUsesUrlParam(): void
    {
        $_GET = ['url' => 'home/show/Carla'];
        $r = $this->router();
        ob_start();
        $result = $r->dispatchFromRequest();
        ob_end_clean();
        $this->assertSame('show:Carla', $result);
    }

    public function testSetControllerNamespaceFluent(): void
    {
        $r = new TRouter;
        $this->assertSame($r, $r->setControllerNamespace('App\\Mvc'));
    }
}
