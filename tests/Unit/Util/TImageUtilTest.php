<?php
declare(strict_types=1);

namespace Tests\Unit\Util;

use Aurea\Util\TImageUtil;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Util\TImageUtil */
final class TImageUtilTest extends TestCase
{
    public function testThumbnailMissingSourceReturnsFalse(): void
    {
        $this->assertFalse(
            TImageUtil::thumbnail(sys_get_temp_dir() . '/aurea_nope_' . uniqid() . '.png', sys_get_temp_dir() . '/aurea_out_' . uniqid() . '.png', 10, 10)
        );
    }

    public function testThumbnail(): void
    {
        $src = sys_get_temp_dir() . '/aurea_src_' . uniqid() . '.png';
        $dest = sys_get_temp_dir() . '/aurea_dst_' . uniqid() . '.png';
        try {
            if (function_exists('imagecreatetruecolor')) {
                $img = imagecreatetruecolor(100, 80);
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefill($img, 0, 0, $white);
                imagepng($img, $src);
                imagedestroy($img);
                $this->assertTrue(TImageUtil::thumbnail($src, $dest, 10, 10));
                $this->assertFileExists($dest);
                $size = getimagesize($dest);
                $this->assertNotFalse($size);
                $this->assertLessThanOrEqual(10, $size[0]);
                $this->assertLessThanOrEqual(10, $size[1]);
            } else {
                file_put_contents($src, 'fallback-content');
                $this->assertTrue(TImageUtil::thumbnail($src, $dest, 10, 10));
                $this->assertSame('fallback-content', (string) file_get_contents($dest));
            }
        } finally {
            if (file_exists($src)) {
                unlink($src);
            }
            if (file_exists($dest)) {
                unlink($dest);
            }
        }
    }

    public function testHumanSize(): void
    {
        $this->assertSame('500 B', TImageUtil::humanSize(500));
        $this->assertSame('1,0 KB', TImageUtil::humanSize(1024));
        $this->assertSame('2,0 KB', TImageUtil::humanSize(2048));
        $this->assertSame('1,0 MB', TImageUtil::humanSize(1048576));
    }

    public function testSanitizeFilename(): void
    {
        $this->assertSame('passwd', TImageUtil::sanitizeFilename('../../etc/passwd'));
        $clean = TImageUtil::sanitizeFilename('meu ficheiro (1).png');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9._-]+$/', $clean);
    }
}
