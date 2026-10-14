<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\TRotatingTxtLogger;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\TRotatingTxtLogger */
final class TRotatingTxtLoggerTest extends TestCase
{
    public function testRotatesBySize(): void
    {
        $file = sys_get_temp_dir() . '/aurea_rot_' . uniqid() . '.log';
        $log = new TRotatingTxtLogger($file, 50, 2);
        for ($i = 0; $i < 10; $i++) {
            $log->write(str_repeat('x', 20) . $i);
        }
        $this->assertFileExists($file);
        $this->assertFileExists($file . '.1');
        unlink($file);
        if (is_file($file . '.1')) {
            unlink($file . '.1');
        }
        if (is_file($file . '.2')) {
            unlink($file . '.2');
        }
    }
}
