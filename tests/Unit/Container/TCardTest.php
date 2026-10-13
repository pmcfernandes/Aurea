<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TCard;
use Aurea\Core\TAction;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TCard */
final class TCardTest extends TestCase
{
    public function testSetImage(): void
    {
        $card = new TCard('Título');
        $this->assertSame($card, $card->setImage('https://exemplo.pt/img.png'));
        $html = $card->getContents();
        $this->assertStringContainsString('card-img-top', $html);
        $this->assertStringContainsString('src="https://exemplo.pt/img.png"', $html);
    }

    public function testSetText(): void
    {
        $card = new TCard('Título');
        $this->assertSame($card, $card->setText('algum texto'));
        $html = $card->getContents();
        $this->assertStringContainsString('card-text', $html);
        $this->assertStringContainsString('algum texto', $html);
    }

    public function testSetContent(): void
    {
        $card = new TCard('Título');
        $this->assertSame($card, $card->setContent('extra'));
        $this->assertStringContainsString('extra', $card->getContents());
    }

    public function testAddButtonWithAction(): void
    {
        $card = new TCard('Título');
        $action = new TAction(['ClienteForm', 'onEdit'], ['key' => 1]);
        $this->assertSame($card, $card->addButton('Editar', $action));
        $html = $card->getContents();
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('btn-primary', $html);
        $this->assertStringContainsString(htmlspecialchars($action->toHref(), ENT_QUOTES), $html);
    }

    public function testAddButtonWithoutAction(): void
    {
        $card = new TCard('Título');
        $card->addButton('Fechar', null, 'btn-secondary');
        $html = $card->getContents();
        $this->assertStringContainsString('Fechar', $html);
        $this->assertStringContainsString('btn-secondary', $html);
        $this->assertStringContainsString('href="#"', $html);
    }
}
