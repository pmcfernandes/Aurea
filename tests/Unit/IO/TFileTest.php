<?php
declare(strict_types=1);

namespace Tests\Unit\IO;

use Aurea\IO\TFile;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\IO\TFile */
final class TFileTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/aurea_file_' . uniqid();
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
        }
        rmdir($this->dir);
    }

    public function testWriteReadExists(): void
    {
        $file = $this->dir . '/sub/a.txt';
        $this->assertTrue(TFile::write($file, 'ola'));
        $this->assertTrue(TFile::exists($file));
        $this->assertSame('ola', TFile::read($file));
        $this->assertFalse(TFile::exists($file . '.x'));
    }

    public function testReadMissingThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TFile::read($this->dir . '/nope.txt');
    }

    public function testExtensionAndSize(): void
    {
        $this->assertSame('pdf', TFile::getExtension('/x/y/doc.pdf'));
        $this->assertSame('500,00 B', TFile::size(500));
        $this->assertSame('2,00 KB', TFile::size(2048));
        $this->assertSame('5,00 MB', TFile::size(5 * 1024 * 1024));
    }

    public function testDirOps(): void
    {
        $this->assertTrue(TFile::isDir($this->dir));
        $this->assertFalse(TFile::isDir($this->dir . '/nope'));
        $sub = $this->dir . '/novo/fundo';
        $this->assertTrue(TFile::createDirectory($sub, true));
        $this->assertTrue(TFile::isDir($sub));
        TFile::write($sub . '/f.txt', 'x');
        $listed = TFile::getDir($this->dir);
        $norm = fn($p) => str_replace('\\', '/', $p);
        $this->assertContains($norm($sub . '/f.txt'), array_map($norm, $listed));
        $this->assertTrue(TFile::delete($sub . '/f.txt'));
        $this->assertFalse(TFile::exists($sub . '/f.txt'));
        $this->assertTrue(TFile::delete($sub));
        $this->assertTrue(TFile::delete($this->dir . '/novo'));
    }

    public function testInfo(): void
    {
        $file = $this->dir . '/i.txt';
        TFile::write($file, '12345');
        $info = TFile::info($file);
        $this->assertSame(5, $info['size']);
        $this->expectException(\RuntimeException::class);
        TFile::info($file . '.missing');
    }

    public function testDownload(): void
    {
        $this->assertFalse(TFile::download($this->dir . '/nope.bin'));
        $file = $this->dir . '/d.bin';
        TFile::write($file, 'dados');
        ob_start();
        $ok = TFile::download($file, 'saida.bin');
        $out = (string) ob_get_clean();
        $this->assertTrue($ok);
        $this->assertSame('dados', $out);
    }
}
