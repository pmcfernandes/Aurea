<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TQrCode;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TQrCode */
final class TQrCodeTest extends TestCase
{
    protected function setUp(): void
    {
        $prop = new \ReflectionProperty(TQrCode::class, 'libIncluded');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    public function testRendersTextAndLibrary(): void
    {
        $qr = new TQrCode('https://exemplo.pt');
        $html = $qr->getContents();
        $this->assertStringContainsString('exemplo.pt', $html);
        $this->assertStringContainsString('qrcode.min.js', $html);
        $this->assertStringContainsString('new QRCode(', $html);
    }

    public function testSetSize(): void
    {
        $qr = new TQrCode('texto');
        $this->assertSame($qr, $qr->setSize(160));
        $html = $qr->getContents();
        $this->assertStringContainsString('width:160', $html);
        $this->assertStringContainsString('height:160', $html);
    }

    public function testSetColors(): void
    {
        $qr = new TQrCode('texto');
        $this->assertSame($qr, $qr->setColors('#111111', '#eeeeee'));
        $html = $qr->getContents();
        $this->assertStringContainsString('#111111', $html);
        $this->assertStringContainsString('#eeeeee', $html);
    }
}
