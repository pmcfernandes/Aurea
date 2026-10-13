<?php
declare(strict_types=1);

namespace Tests\Unit\Http;

use Aurea\Http\THttpResponse;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Http\THttpResponse */
final class THttpResponseTest extends TestCase
{
    public function testJson(): void
    {
        $res = THttpResponse::json(['ok' => true, 'n' => 1], 201);
        $this->assertSame(201, $res->getStatus());
        $this->assertSame(['ok' => true, 'n' => 1], json_decode($res->getBody(), true));
        $this->assertSame('application/json; charset=UTF-8', $res->getHeaders()['Content-Type']);
    }

    public function testDownloadWithTempFile(): void
    {
        $file = sys_get_temp_dir() . '/aurea_dl_' . uniqid() . '.txt';
        file_put_contents($file, 'conteudo');
        try {
            $res = THttpResponse::download($file);
            $this->assertSame(200, $res->getStatus());
            $this->assertSame('conteudo', $res->getBody());
            $this->assertStringContainsString('attachment;', $res->getHeaders()['Content-Disposition']);
            $this->assertStringContainsString(basename($file), $res->getHeaders()['Content-Disposition']);
        } finally {
            unlink($file);
        }
    }

    public function testDownloadCustomNameAndMime(): void
    {
        $file = sys_get_temp_dir() . '/aurea_dl_' . uniqid() . '.bin';
        file_put_contents($file, 'abc');
        try {
            $res = THttpResponse::download($file, 'relatorio.pdf', 'application/pdf');
            $this->assertSame('application/pdf', $res->getHeaders()['Content-Type']);
            $this->assertStringContainsString('relatorio.pdf', $res->getHeaders()['Content-Disposition']);
            $this->assertSame('3', $res->getHeaders()['Content-Length']);
        } finally {
            unlink($file);
        }
    }

    public function testDownloadMissingThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        THttpResponse::download(sys_get_temp_dir() . '/aurea_missing_' . uniqid() . '.txt');
    }

    public function testSendEchoesBody(): void
    {
        $res = new THttpResponse('ola', 200, ['X-T' => 'v']);
        ob_start();
        $res->send();
        $out = (string) ob_get_clean();
        $this->assertSame('ola', $out);
    }

    public function testRedirect(): void
    {
        $res = THttpResponse::redirect('/destino');
        $this->assertSame(302, $res->getStatus());
        $this->assertSame('/destino', $res->getHeaders()['Location']);
        $moved = THttpResponse::redirect('/old', 301);
        $this->assertSame(301, $moved->getStatus());
    }
}
