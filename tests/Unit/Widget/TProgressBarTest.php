<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TProgressBar;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TProgressBar */
final class TProgressBarTest extends TestCase
{
    public function testRendersPercent(): void
    {
        $p = new TProgressBar(50);
        $html = $p->getContents();
        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('50%', $html);
        $this->assertStringContainsString('aria-valuenow="50"', $html);
    }
}
