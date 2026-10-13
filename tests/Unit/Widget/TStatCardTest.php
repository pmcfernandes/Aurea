<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Core\TAction;
use Aurea\Widget\TStatCard;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TStatCard */
final class TStatCardTest extends TestCase
{
    public function testRendersTitleAndValue(): void
    {
        $card = new TStatCard('Vendas', 1250);
        $html = $card->getContents();
        $this->assertStringContainsString('Vendas', $html);
        $this->assertStringContainsString('1250', $html);
    }

    public function testSetIcon(): void
    {
        $card = new TStatCard('V', 1);
        $this->assertSame($card, $card->setIcon('fa-cart-shopping'));
        $this->assertStringContainsString('fa-cart-shopping', $card->getContents());
    }

    public function testSetColor(): void
    {
        $card = new TStatCard('V', 1);
        $this->assertSame($card, $card->setColor('#198754'));
        $this->assertStringContainsString('#198754', $card->getContents());
    }

    public function testSetTrendUp(): void
    {
        $card = new TStatCard('V', 1);
        $this->assertSame($card, $card->setTrend('+12%', 'up'));
        $html = $card->getContents();
        $this->assertStringContainsString('+12%', $html);
        $this->assertStringContainsString('▲', $html);
    }

    public function testSetTrendDown(): void
    {
        $card = new TStatCard('V', 1);
        $card->setTrend('-5%', 'down');
        $html = $card->getContents();
        $this->assertStringContainsString('-5%', $html);
        $this->assertStringContainsString('▼', $html);
    }

    public function testSetAction(): void
    {
        $card = new TStatCard('V', 1);
        $action = new TAction(['HomeView', 'onShow']);
        $this->assertSame($card, $card->setAction($action));
        $html = $card->getContents();
        $this->assertStringContainsString('stretched-link', $html);
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $html);
        $card->setAction(null);
        $this->assertStringNotContainsString('stretched-link', $card->getContents());
    }
}
