<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TNumericIndicator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TNumericIndicator */
final class TNumericIndicatorTest extends TestCase
{
    public function testFormatsNumberAndCurrency(): void
    {
        $indicator = (new TNumericIndicator(1234.5))->setFormat(2)->setCurrency('eur');
        $html = $indicator->getContents();
        $this->assertStringContainsString('1.234,50 EUR', $html);
        $this->assertStringContainsString('value="1234.5"', $html);
    }

    public function testRejectsNegativeDecimals(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TNumericIndicator)->setFormat(-1);
    }
}
