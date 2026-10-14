<?php
declare(strict_types=1);

namespace Tests\Unit\Service;

use Aurea\Http\THttpRequest;
use Aurea\Service\TUploadService;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Service\TUploadService */
final class TUploadServiceTest extends TestCase
{
    private array $filesBackup = [];
    private string $directory;

    protected function setUp(): void
    {
        $this->filesBackup = $_FILES;
        $_FILES = [];
        $this->directory = sys_get_temp_dir() . '/aurea_upload_service_' . uniqid();
    }

    protected function tearDown(): void
    {
        $_FILES = $this->filesBackup;
        foreach ((array) glob($this->directory . '/*') as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
    }

    public function testRequiresValidTokenBeforeSaving(): void
    {
        $service = new TUploadService($this->directory, ['txt'], 1024, ['text/plain'], fn(string $token) => $token === 'ok');
        $response = $service->handle(new THttpRequest('POST', '/', ['token' => 'no']));
        $this->assertSame(401, $response->getStatus());
    }

    public function testStoresAuthorizedUpload(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aurea_upload_service_');
        file_put_contents((string) $tmp, 'conteudo');
        $_FILES['file'] = ['name' => 'teste.txt', 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => filesize($tmp)];
        $service = new TUploadService($this->directory, ['txt'], 1024, ['text/plain'], fn(string $token) => $token === 'ok');
        $response = $service->handle(new THttpRequest('POST', '/', ['token' => 'ok']));
        $payload = json_decode($response->getBody(), true);
        $this->assertSame(201, $response->getStatus());
        $this->assertFileExists($payload['data']['path']);
    }
}
