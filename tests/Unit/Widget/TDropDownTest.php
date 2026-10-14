<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Core\TAction;
use Aurea\Widget\TDropDown;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TDropDown */
final class TDropDownTest extends TestCase
{
    public function testRendersAccessibleMenuSemantics(): void
    {
        $dropDown = new TDropDown('Opções');
        $dropDown->addAction('Editar', new TAction(['ClienteForm', 'onEdit']));

        $html = $dropDown->getContents();
        $this->assertStringContainsString("aria-haspopup='true'", $html);
        $this->assertStringContainsString("role='menu'", $html);
        $this->assertStringContainsString("role='menuitem'", $html);
    }
    public function testAddAction(): void
    {
        $dd = new TDropDown('Opções', 'btn-primary');
        $action = new TAction(['ClienteForm', 'onEdit'], ['key' => 1]);
        $this->assertSame($dd, $dd->addAction('Editar', $action, 'fa-pen'));
        $html = $dd->getContents();
        $this->assertStringContainsString('Opções', $html);
        $this->assertStringContainsString('btn-primary', $html);
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('fa-pen', $html);
        $this->assertStringContainsString('dropdown-item', $html);
        $this->assertStringContainsString(htmlspecialchars($action->serialize(), ENT_QUOTES), $html);
    }

    public function testAddActionWithoutIcon(): void
    {
        $dd = new TDropDown('Menu');
        $dd->addAction('Ver', new TAction(['Pagina', 'onVer']));
        $html = $dd->getContents();
        $this->assertStringContainsString('Ver', $html);
        $this->assertStringNotContainsString('<i ', $html);
    }

    public function testEscapesLabel(): void
    {
        $dd = new TDropDown('<Opções>');
        $dd->addAction('<Item>', new TAction(['Pagina', 'onVer']));
        $html = $dd->getContents();
        $this->assertStringContainsString('&lt;Opções&gt;', $html);
        $this->assertStringContainsString('&lt;Item&gt;', $html);
    }
}
