<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TFullCalendar;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TFullCalendar */
final class TFullCalendarTest extends TestCase
{
    protected function setUp(): void
    {
        $prop = new \ReflectionProperty(TFullCalendar::class, 'libIncluded');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    public function testSetEvents(): void
    {
        $cal = new TFullCalendar;
        $events = [['title' => 'Reuniao', 'start' => '2026-10-20']];
        $this->assertSame($cal, $cal->setEvents($events));
        $html = $cal->getContents();
        $this->assertStringContainsString('Reuniao', $html);
        $this->assertStringContainsString('2026-10-20', $html);
        $this->assertStringContainsString('FullCalendar.Calendar', $html);
    }

    public function testSetEventsUrl(): void
    {
        $cal = new TFullCalendar;
        $this->assertSame($cal, $cal->setEventsUrl('https://exemplo.pt/eventos.json'));
        $this->assertStringContainsString('eventos.json', $cal->getContents());
    }

    public function testSetInitialView(): void
    {
        $cal = new TFullCalendar;
        $this->assertSame($cal, $cal->setInitialView('timeGridWeek'));
        $this->assertStringContainsString('timeGridWeek', $cal->getContents());
    }

    public function testSetLocale(): void
    {
        $cal = new TFullCalendar;
        $this->assertSame($cal, $cal->setLocale('en'));
        $this->assertStringContainsString('"locale":"en"', $cal->getContents());
    }
}
