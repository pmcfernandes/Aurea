<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TUploader;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TUploader */
final class TUploaderTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $filesBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];
    /** @var string[] */
    private array $created = [];
    private string $targetDir;

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->filesBackup = $_FILES;
        $this->sessionBackup = $_SESSION ?? [];
        $_FILES = [];
        $this->targetDir = sys_get_temp_dir() . '/aurea_uploader_test';
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_FILES = $this->filesBackup;
        $_SESSION = $this->sessionBackup;
        foreach ($this->created as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        if (is_dir($this->targetDir)) {
            foreach ((array) glob($this->targetDir . '/*') as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($this->targetDir);
        }
    }

    private function makeUpload(string $field, string $name, string $content = 'conteudo'): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aurea_up_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $content);
        $_FILES[$field] = [
            'name' => $name,
            'type' => 'text/plain',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmp),
        ];
    }

    public function testSaveReturnsNullWithoutFile(): void
    {
        unset($_FILES['anexo']);
        $this->assertNull(TUploader::save('anexo', $this->targetDir));
        $_FILES['anexo'] = ['error' => UPLOAD_ERR_NO_FILE];
        $this->assertNull(TUploader::save('anexo', $this->targetDir));
    }

    public function testSaveMovesUploadedFile(): void
    {
        $this->makeUpload('anexo', 'doc.txt');
        $dest = TUploader::save('anexo', $this->targetDir);
        $this->assertNotNull($dest);
        $this->created[] = $dest;
        $this->assertFileExists($dest);
        $this->assertSame('conteudo', file_get_contents($dest));
    }

    public function testSaveWithAllowedExtensions(): void
    {
        $this->makeUpload('anexo', 'doc.txt');
        $dest = TUploader::save('anexo', $this->targetDir, ['txt']);
        $this->assertNotNull($dest);
        $this->created[] = $dest;
        $this->assertFileExists($dest);
    }

    public function testSaveValidatesDetectedMimeType(): void
    {
        $this->makeUpload('anexo', 'doc.txt');
        $dest = TUploader::save('anexo', $this->targetDir, ['txt'], 10485760, ['text/plain']);
        $this->assertNotNull($dest);
        $this->created[] = $dest;

        $this->makeUpload('bin', 'doc.txt', 'GIF89a');
        $this->expectException(\RuntimeException::class);
        TUploader::save('bin', $this->targetDir, ['txt'], 10485760, ['text/plain']);
    }

    public function testSaveImageRejectsNonImageContent(): void
    {
        $this->makeUpload('imagem', 'imagem.png', 'não é uma imagem');
        $this->expectException(\RuntimeException::class);
        TUploader::saveImage('imagem', $this->targetDir);
    }

    public function testSaveThrowsOnForbiddenExtension(): void
    {
        $this->makeUpload('anexo', 'virus.exe');
        $this->expectException(\RuntimeException::class);
        TUploader::save('anexo', $this->targetDir, ['txt', 'pdf']);
    }

    public function testSaveThrowsOnSizeExceeded(): void
    {
        $this->makeUpload('anexo', 'doc.txt', '1234567890');
        $this->expectException(\RuntimeException::class);
        TUploader::save('anexo', $this->targetDir, null, 5);
    }

    public function testSaveThrowsOnUploadError(): void
    {
        $_FILES['anexo'] = [
            'name' => 'doc.txt',
            'type' => 'text/plain',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_PARTIAL,
            'size' => 0,
        ];
        $this->expectException(\RuntimeException::class);
        TUploader::save('anexo', $this->targetDir);
    }

    public function testSaveMultipleMovesFiles(): void
    {
        $tmp1 = tempnam(sys_get_temp_dir(), 'aurea_up_');
        $tmp2 = tempnam(sys_get_temp_dir(), 'aurea_up_');
        $this->assertNotFalse($tmp1);
        $this->assertNotFalse($tmp2);
        file_put_contents($tmp1, 'um');
        file_put_contents($tmp2, 'dois');
        $_FILES['anexos'] = [
            'name' => ['a.txt', 'b.txt'],
            'type' => ['text/plain', 'text/plain'],
            'tmp_name' => [$tmp1, $tmp2],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [filesize($tmp1), filesize($tmp2)],
        ];
        $dests = TUploader::saveMultiple('anexos', $this->targetDir);
        $this->created = array_merge($this->created, $dests);
        $this->assertCount(2, $dests);
        foreach ($dests as $dest) {
            $this->assertFileExists($dest);
        }
    }

    public function testSaveMultipleReturnsEmptyWithoutFiles(): void
    {
        unset($_FILES['anexos']);
        $this->assertSame([], TUploader::saveMultiple('anexos', $this->targetDir));
        $_FILES['anexos'] = ['name' => 'nao-array'];
        $this->assertSame([], TUploader::saveMultiple('anexos', $this->targetDir));
    }

    public function testSaveMultipleSkipsNoFileEntries(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aurea_up_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, 'ok');
        $_FILES['anexos'] = [
            'name' => ['vazio.txt', 'cheio.txt'],
            'type' => ['', 'text/plain'],
            'tmp_name' => ['', $tmp],
            'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK],
            'size' => [0, filesize($tmp)],
        ];
        $dests = TUploader::saveMultiple('anexos', $this->targetDir);
        $this->created = array_merge($this->created, $dests);
        $this->assertCount(1, $dests);
    }

    public function testSaveMultipleThrowsOnForbiddenExtension(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aurea_up_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, 'x');
        $_FILES['anexos'] = [
            'name' => ['virus.exe'],
            'type' => ['application/octet-stream'],
            'tmp_name' => [$tmp],
            'error' => [UPLOAD_ERR_OK],
            'size' => [filesize($tmp)],
        ];
        try {
            TUploader::saveMultiple('anexos', $this->targetDir, ['txt']);
            $this->fail('Era esperada RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('não permitida', $e->getMessage());
        }
    }
}
