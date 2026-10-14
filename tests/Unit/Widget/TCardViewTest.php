<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Core\TAction;
use Aurea\Widget\TCardView;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TCardView */
final class TCardViewTest extends TestCase
{
    public function testCardsAndSearch(): void
    {
        $v = new TCardView();
        $v->addCard('Alpha', '<p>primeiro</p>');
        $v->addCard('Beta', '<p>segundo</p>', new TAction(['HomeView', 'onShow']));
        $this->assertSame(2, $v->count());
        $this->assertStringContainsString('Alpha', $v->getContents());
        $v->setSearch('beta');
        $html = $v->getContents();
        $this->assertStringNotContainsString('Alpha', $html);
        $this->assertStringContainsString('Beta', $html);
    }
}
