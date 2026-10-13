<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TAction;
use PHPUnit\Framework\TestCase;

final class DummyActionController
{
    public function onHello(array $p): string
    {
        return 'hi ' . $p['n'];
    }
}

/** @covers \Aurea\Core\TAction */
final class TActionTest extends TestCase
{
    public function testSerialize(): void
    {
        $a = new TAction(['ClienteList', 'onReload'], ['order' => 'nome']);
        $this->assertSame('index.php?class=ClienteList&method=onReload&order=nome', $a->serialize());
    }

    public function testStringAction(): void
    {
        $a = new TAction('App\Control\HomeView::onShow');
        $this->assertSame('App\Control\HomeView', $a->getController());
        $this->assertSame('onShow', $a->getMethod());
        $this->assertTrue($a->isStatic());
    }

    public function testParameters(): void
    {
        $a = new TAction(['X', 'onY']);
        $a->setParameter('a', 1)->setParameters(['b' => 2]);
        $this->assertSame(1, $a->getParameter('a'));
        $this->assertSame(['a' => 1, 'b' => 2], $a->getParameters());
        $this->assertNull($a->getParameter('missing'));
        $this->assertSame('X', $a->getController());
        $this->assertSame('onY', $a->getMethod());
    }

    public function testObjectControllerIsNonStatic(): void
    {
        $a = new TAction([new DummyActionController, 'onHello']);
        $this->assertFalse($a->isStatic());
        $this->assertSame('Tests\Unit\Core\DummyActionController', $a->getController());
    }

    public function testHrefOnClickAjax(): void
    {
        $a = new TAction(['X', 'onY'], ['k' => 1]);
        $this->assertSame($a->serialize(), $a->toHref());
        $this->assertStringContainsString('window.location=', $a->toOnClick());
        $this->assertStringContainsString('confirm(', $a->toOnClick('Sure?'));
        $this->assertStringContainsString('__aureaAjax', $a->toAjax('#alvo'));
    }

    public function testExecute(): void
    {
        $a = new TAction([DummyActionController::class, 'onHello'], ['n' => 'bob']);
        $this->assertSame('hi bob', $a->execute());
    }

    public function testExecuteMissingController(): void
    {
        $this->expectException(\RuntimeException::class);
        (new TAction(['Nope_Missing_Xyz', 'onX']))->execute();
    }

    public function testInvalidAction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TAction(123);
    }
}
