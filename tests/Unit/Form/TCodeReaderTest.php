<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TCodeReader;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TCodeReader */
final class TCodeReaderTest extends TestCase
{
    public function testRendersCameraReaderWithSafeFormats(): void
    {
        $reader = (new TCodeReader('code'))->setFormats(['qr_code', 'code_128']);
        $html = $reader->getContents();
        $this->assertStringContainsString('BarcodeDetector', $html);
        $this->assertStringContainsString("name='code'", $html);
        $this->assertStringContainsString("aria-live='polite'", $html);
    }

    public function testRequiresAtLeastOneFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TCodeReader('code'))->setFormats([]);
    }
}
