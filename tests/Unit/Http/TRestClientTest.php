<?php
declare(strict_types=1);

namespace Tests\Unit\Http;

use Aurea\Http\TRestClient;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Http\TRestClient */
final class TRestClientTest extends TestCase
{
    public function testClosedPortReturnsStatusZeroWithoutFatal(): void
    {
        $client = new TRestClient('http://127.0.0.1:1');
        $result = $client->get('/ping');
        $this->assertSame(0, $result['status']);
        $this->assertNull($result['data']);
        $this->assertSame(0, $client->getLastStatus());
    }

    public function testPostClosedPortReturnsStatusZero(): void
    {
        $client = new TRestClient('http://127.0.0.1:1');
        $result = $client->post('/ping', ['a' => 1]);
        $this->assertSame(0, $result['status']);
        $this->assertNull($result['data']);
        $this->assertSame(0, $client->getLastStatus());
    }
}
