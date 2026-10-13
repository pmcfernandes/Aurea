<?php
declare(strict_types=1);

namespace Tests\Unit\Widget;

use Aurea\Widget\TImageCropper;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Widget\TImageCropper */
final class TImageCropperTest extends TestCase
{
    private string $targetDir = '';

    protected function tearDown(): void
    {
        if ($this->targetDir !== '' && is_dir($this->targetDir)) {
            foreach (glob($this->targetDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->targetDir);
        }
        $this->targetDir = '';
    }

    public function testSetAspectRatio(): void
    {
        $crop = new TImageCropper('foto');
        $this->assertSame($crop, $crop->setAspectRatio(1.5));
        $html = $crop->getContents();
        $this->assertStringContainsString('aspectRatio:1.5', $html);
        $this->assertStringContainsString("name='foto'", $html);
        $this->assertStringContainsString("name='foto_data'", $html);
    }

    public function testSetAspectRatioNullIsFree(): void
    {
        $crop = new TImageCropper('foto');
        $crop->setAspectRatio(null);
        $this->assertStringContainsString('aspectRatio:NaN', $crop->getContents());
    }

    public function testSaveDataUrlValid(): void
    {
        $binary = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $dataUrl = 'data:image/png;base64,' . base64_encode((string) $binary);
        $this->targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aurea_crop_' . uniqid();
        $path = TImageCropper::saveDataUrl($dataUrl, $this->targetDir);
        $this->assertFileExists($path);
        $this->assertStringEndsWith('.png', $path);
        $this->assertSame((string) $binary, (string) file_get_contents($path));
    }

    public function testSaveDataUrlInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TImageCropper::saveDataUrl('não-é-uma-data-url', sys_get_temp_dir());
    }

    public function testSaveDataUrlBadBase64Throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TImageCropper::saveDataUrl('data:image/png;base64,!!!não-base64!!!', sys_get_temp_dir());
    }
}
