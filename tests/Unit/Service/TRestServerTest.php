<?php
declare(strict_types=1);

namespace Tests\Unit\Service;

use Aurea\Http\THttpRequest;
use Aurea\Service\TRestServer;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Service\TRestServer */
final class TRestServerTest extends TestCase
{
    private function server(): TRestServer
    {
        $server = new TRestServer();
        $server->addService('Calc', new class {
            public function onPing($p)
            {
                return ['pong' => true, 'x' => $p['x'] ?? null];
            }

            public function onFail($p)
            {
                throw new \RuntimeException('boom');
            }
        });
        return $server;
    }

    public function testAddServiceIsFluent(): void
    {
        $server = new TRestServer();
        $this->assertSame($server, $server->addService('A', new class {
            public function onPing($p)
            {
                return 'ok';
            }
        }));
    }

    public function testHandleSuccessViaQuery(): void
    {
        $req = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onPing', 'x' => '5']);
        $out = $this->server()->handle($req);
        $this->assertSame(['pong' => true, 'x' => '5'], json_decode($out, true));
    }

    public function testHandleSuccessViaJsonBody(): void
    {
        $req = new THttpRequest(
            'POST',
            '/',
            [],
            [],
            (string) json_encode(['service' => 'Calc', 'method' => 'onPing', 'params' => ['x' => '9']])
        );
        $out = $this->server()->handle($req);
        $this->assertSame(['pong' => true, 'x' => '9'], json_decode($out, true));
    }

    public function testMissingServiceError(): void
    {
        $req = new THttpRequest('GET', '/', ['method' => 'onPing']);
        $out = json_decode($this->server()->handle($req), true);
        $this->assertArrayHasKey('error', $out);
    }

    public function testMissingMethodError(): void
    {
        $req = new THttpRequest('GET', '/', ['service' => 'Calc']);
        $out = json_decode($this->server()->handle($req), true);
        $this->assertArrayHasKey('error', $out);
    }

    public function testUnknownServiceError(): void
    {
        $req = new THttpRequest('GET', '/', ['service' => 'Nope', 'method' => 'onPing']);
        $out = json_decode($this->server()->handle($req), true);
        $this->assertArrayHasKey('error', $out);
        $this->assertStringContainsString('Nope', $out['error']);
    }

    public function testUnknownMethodError(): void
    {
        $req = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onMissing']);
        $out = json_decode($this->server()->handle($req), true);
        $this->assertArrayHasKey('error', $out);
    }

    public function testHandlerExceptionBecomesError(): void
    {
        $req = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onFail']);
        $out = json_decode($this->server()->handle($req), true);
        $this->assertSame(['error' => 'boom'], $out);
    }
}
