<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TSignaturePad;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TSignaturePad */
final class TSignaturePadTest extends TestCase
{
    public function testRendersCanvas(): void
    {
        $s = new TSignaturePad('ass');
        $html = $s->getContents();
        $this->assertStringContainsString('<canvas', $html);
        $this->assertStringContainsString('name="ass"', $html);
    }
}
