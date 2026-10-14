<?php
declare(strict_types=1);

namespace Tests\Unit\Service;

use Aurea\Http\THttpRequest;
use Aurea\Service\TRestServer;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Service\TRestServer */
final class TRestServerSecurityTest extends TestCase
{
    private function server(): TRestServer
    {
        $s = new TRestServer();
        $s->addService('Calc', new class {
            public function onPing($p)
            {
                return ['pong' => true];
            }
        });
        return $s;
    }

    protected function tearDown(): void
    {
        TRestServer::resetRateLimit();
        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testAuthBlocks(): void
    {
        $s = $this->server()->setAuthCallback(fn() => 'denied');
        $req = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onPing']);
        $out = json_decode($s->handle($req), true);
        $this->assertSame('denied', $out['error']);
        $this->assertSame(401, $out['code']);
    }

    public function testRateLimit(): void
    {
        $s = $this->server()->setRateLimit(1, 60);
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $req = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onPing']);
        $first = json_decode($s->handle($req), true);
        $this->assertArrayHasKey('data', $first);
        $second = json_decode($s->handle($req), true);
        $this->assertSame(429, $second['code']);
    }

    public function testOpenApi(): void
    {
        $api = $this->server()->openApi('Test', '1.2.3');
        $this->assertSame('3.0.0', $api['openapi']);
        $this->assertArrayHasKey('/Calc/onPing', $api['paths']);
    }
}
