<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use Aurea\Core\TAction;
use Aurea\Grid\TRowActionGroup;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TRowActionGroup */
final class TRowActionGroupTest extends TestCase
{
    public function testGetLabel(): void
    {
        $this->assertSame('Ações', (new TRowActionGroup)->getLabel());
        $this->assertSame('Opções', (new TRowActionGroup('Opções'))->getLabel());
    }

    public function testAddActionReturnsStatic(): void
    {
        $group = new TRowActionGroup;
        $this->assertSame($group, $group->addAction('Editar', new TAction(['ClienteForm', 'onEdit'])));
    }

    public function testRenderCellWithKey(): void
    {
        $group = new TRowActionGroup('Opções');
        $group->addAction('Editar', new TAction(['ClienteForm', 'onEdit']), 'fa-pen');
        $html = $group->renderCell(['id' => 12, 'nome' => 'Ana']);
        $this->assertStringContainsString('<td>', $html);
        $this->assertStringContainsString('dropdown', $html);
        $this->assertStringContainsString('Opções', $html);
        $this->assertStringContainsString('Editar', $html);
        $this->assertStringContainsString('key=12', $html);
    }

    public function testRenderCellRespectsDisplayCallback(): void
    {
        $group = new TRowActionGroup;
        $group->addAction('Visível', new TAction(['A', 'onShow']));
        $group->addAction('Oculta', new TAction(['A', 'onHide']), '', fn(array $row) => false);
        $html = $group->renderCell(['id' => 1]);
        $this->assertStringContainsString('onShow', $html);
        $this->assertStringNotContainsString('onHide', $html);
    }

    public function testRenderCellWithoutIdHasNoKey(): void
    {
        $group = new TRowActionGroup;
        $group->addAction('Ver', new TAction(['A', 'onView']));
        $html = $group->renderCell(['nome' => 'Ana']);
        $this->assertStringContainsString('onView', $html);
        $this->assertStringNotContainsString('key=', $html);
    }
}
