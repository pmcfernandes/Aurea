<?php
declare(strict_types=1);

namespace Tests\Unit\Http;

use Aurea\Http\THttpRequest;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Http\THttpRequest */
final class THttpRequestTest extends TestCase
{
    private array $serverBackup = [];
    private array $getBackup = [];

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
    }

    public function testConstructorDefaults(): void
    {
        $req = new THttpRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/', $req->getPath());
        $this->assertSame([], $req->getQuery());
        $this->assertSame([], $req->getHeaders());
        $this->assertSame('', $req->getBody());
        $this->assertNull($req->getJson());
    }

    public function testConstructorInjectable(): void
    {
        $req = new THttpRequest('post', '/api/x', ['a' => '1'], ['X-T' => 'v'], '{"k":"w"}');
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/x', $req->getPath());
        $this->assertSame(['a' => '1'], $req->getQuery());
        $this->assertSame(['X-T' => 'v'], $req->getHeaders());
        $this->assertSame('{"k":"w"}', $req->getBody());
        $this->assertSame(['k' => 'w'], $req->getJson());
    }

    public function testGetJsonInvalidReturnsNull(): void
    {
        $req = new THttpRequest('POST', '/', [], [], 'not json');
        $this->assertNull($req->getJson());
    }

    public function testGetPrefersQueryThenJsonThenDefault(): void
    {
        $req = new THttpRequest('POST', '/', ['k' => 'query'], [], '{"k":"body","j":"jb","params":{"p":"pv"}}');
        $this->assertSame('query', $req->get('k'));
        $this->assertSame('jb', $req->get('j'));
        $this->assertSame('pv', $req->get('p'));
        $this->assertSame('d', $req->get('missing', 'd'));
    }

    public function testCaptureWithSimulatedServer(): void
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'post',
            'REQUEST_URI' => '/api/clientes?nome=Ana&cidade=Porto',
            'HTTP_X_TEST' => 'yes',
            'CONTENT_TYPE' => 'application/json',
        ];
        $_GET = ['nome' => 'Ana', 'cidade' => 'Porto'];
        $req = THttpRequest::capture();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/clientes', $req->getPath());
        $this->assertSame(['nome' => 'Ana', 'cidade' => 'Porto'], $req->getQuery());
        $this->assertSame('Ana', $req->get('nome'));
        $headers = $req->getHeaders();
        $this->assertArrayHasKey('X-Test', $headers);
        $this->assertSame('yes', $headers['X-Test']);
    }
}
