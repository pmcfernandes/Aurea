<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Core\TAction;
use Aurea\Widget\TKanban;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TKanban */
final class TKanbanTest extends TestCase
{
    protected function setUp(): void
    {
        $prop = new \ReflectionProperty(TKanban::class, 'libIncluded');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    public function testAddColumn(): void
    {
        $kanban = new TKanban;
        $this->assertSame($kanban, $kanban->addColumn('todo', 'Por fazer'));
        $html = $kanban->getContents();
        $this->assertStringContainsString('Por fazer', $html);
        $this->assertStringContainsString("data-status='todo'", $html);
    }

    public function testAddCard(): void
    {
        $kanban = new TKanban;
        $kanban->addColumn('todo', 'Por fazer');
        $this->assertSame($kanban, $kanban->addCard('todo', '1', 'Tarefa', 'Detalhes'));
        $html = $kanban->getContents();
        $this->assertStringContainsString('Tarefa', $html);
        $this->assertStringContainsString('Detalhes', $html);
        $this->assertStringContainsString("data-id='1'", $html);
    }

    public function testAddCardCreatesMissingColumn(): void
    {
        $kanban = new TKanban;
        $kanban->addCard('nova', '9', 'Auto');
        $html = $kanban->getContents();
        $this->assertStringContainsString("data-status='nova'", $html);
        $this->assertStringContainsString('Auto', $html);
    }

    public function testSetMoveAction(): void
    {
        $kanban = new TKanban;
        $kanban->addColumn('todo', 'Por fazer');
        $action = new TAction(['TarefaList', 'onMoveCard']);
        $this->assertSame($kanban, $kanban->setMoveAction($action));
        $html = $kanban->getContents();
        $this->assertStringContainsString('fetch(', $html);
        $this->assertStringContainsString($action->serialize(), $html);
    }

    public function testEscapesCardContent(): void
    {
        $kanban = new TKanban;
        $kanban->addCard('todo', '1', '<b>X</b>', '<i>Y</i>');
        $html = $kanban->getContents();
        $this->assertStringContainsString('&lt;b&gt;X&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>X</b>', $html);
    }
}
