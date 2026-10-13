<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TTimeline;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TTimeline */
final class TTimelineTest extends TestCase
{
    public function testAddEvent(): void
    {
        $timeline = new TTimeline;
        $this->assertSame($timeline, $timeline->addEvent('2026-10-13', 'Lançamento', 'Versão 1.0 publicada.'));
        $html = $timeline->getContents();
        $this->assertStringContainsString('2026-10-13', $html);
        $this->assertStringContainsString('Lançamento', $html);
        $this->assertStringContainsString('Versão 1.0 publicada.', $html);
        $this->assertStringContainsString('aurea-timeline-item', $html);
    }

    public function testAddEventWithIconAndColor(): void
    {
        $timeline = new TTimeline;
        $timeline->addEvent('2026-01-01', 'Marco', '', 'fa-flag', '#ff0000');
        $html = $timeline->getContents();
        $this->assertStringContainsString('fa-flag', $html);
        $this->assertStringContainsString('#ff0000', $html);
        $this->assertStringNotContainsString('aurea-timeline-body', $html);
    }

    public function testEscapesContent(): void
    {
        $timeline = new TTimeline;
        $timeline->addEvent('<data>', '<b>Título</b>', '<script>alert(1)</script>');
        $html = $timeline->getContents();
        $this->assertStringContainsString('&lt;b&gt;Título&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<b>Título</b>', $html);
    }
}
