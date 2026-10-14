<?php
declare(strict_types=1);

namespace Tests\Unit\Service;

use Aurea\Http\THttpRequest;
use Aurea\Http\THttpResponse;
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
        $this->assertSame(['data' => ['pong' => true, 'x' => '5']], json_decode($out, true));
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
        $this->assertSame(['data' => ['pong' => true, 'x' => '9']], json_decode($out, true));
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
        $this->assertSame('boom', $out['error']);
        $this->assertArrayHasKey('code', $out);
    }

    public function testRoutesAndStructuredResponse(): void
    {
        $server = $this->server()->addRoute('GET', '/api/ping', 'Calc', 'onPing');
        $response = $server->handleResponse(new THttpRequest('GET', '/api/ping', ['x' => 'route']));
        $this->assertInstanceOf(THttpResponse::class, $response);
        $this->assertSame(200, $response->getStatus());
        $this->assertSame(['data' => ['pong' => true, 'x' => 'route']], json_decode($response->getBody(), true));
    }

    public function testRejectsNonEventMethodsUnlessExplicitlyAllowed(): void
    {
        $server = new TRestServer();
        $server->addService('Svc', new class {
            public function helper(array $param): array { return ['ok' => true]; }
        });
        $req = new THttpRequest('GET', '/', ['service' => 'Svc', 'method' => 'helper']);
        $this->assertSame(404, $server->handleResponse($req)->getStatus());

        $server->setServiceMethods('Svc', ['helper']);
        $this->assertSame(200, $server->handleResponse($req)->getStatus());
    }

    public function testRequestSchemaIsValidatedAndPublishedInOpenApi(): void
    {
        $server = $this->server()
            ->addRoute('POST', '/api/ping', 'Calc', 'onPing')
            ->setServiceSchema('Calc', 'onPing', [
                'required' => ['x'],
                'properties' => ['x' => ['type' => 'integer']],
            ], ['type' => 'object']);
        $invalid = $server->handleResponse(new THttpRequest('POST', '/api/ping', ['x' => 'not-number']));
        $this->assertSame(422, $invalid->getStatus());
        $valid = $server->handleResponse(new THttpRequest('POST', '/api/ping', ['x' => '7']));
        $this->assertSame(200, $valid->getStatus());
        $operation = $server->openApi()['paths']['/api/ping']['post'];
        $this->assertSame('integer', $operation['requestBody']['content']['application/json']['schema']['properties']['x']['type']);
        $this->assertSame('object', $operation['responses']['200']['content']['application/json']['schema']['type']);
    }

    public function testDatabaseRateLimitIsSharedBetweenServers(): void
    {
        $connection = new \PDO('sqlite::memory:');
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.77';
        try {
            $one = $this->server()->setRateLimit(1)->setRateLimitDatabase($connection);
            $two = $this->server()->setRateLimit(1)->setRateLimitDatabase($connection);
            $request = new THttpRequest('GET', '/', ['service' => 'Calc', 'method' => 'onPing']);
            $this->assertSame(200, $one->handleResponse($request)->getStatus());
            $this->assertSame(429, $two->handleResponse($request)->getStatus());
        } finally {
            unset($_SERVER['REMOTE_ADDR']);
        }
    }
}
