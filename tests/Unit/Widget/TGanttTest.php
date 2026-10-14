<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TGantt;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TGantt */
final class TGanttTest extends TestCase
{
    public function testRendersTaskProgress(): void
    {
        $gantt = (new TGantt)->setTasks([
            ['label' => 'Planeamento', 'start' => '2026-01-01', 'end' => '2026-01-11', 'progress' => 50],
            ['label' => 'Entrega', 'start' => '2026-01-11', 'end' => '2026-01-21'],
        ]);
        $html = $gantt->getContents();
        $this->assertStringContainsString('Planeamento', $html);
        $this->assertStringContainsString('50% concluído', $html);
        $this->assertStringContainsString("width:50%", $html);
    }

    public function testRejectsInvalidTask(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TGantt)->setTasks([['label' => 'Inválida', 'start' => 'nope', 'end' => '2026-01-01']]);
    }
}
