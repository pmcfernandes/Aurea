<?php
declare(strict_types=1);

namespace Tests\Unit\Http;

use Aurea\Http\TRestClient;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Http\TRestClient */
final class TRestClientOptionsTest extends TestCase
{
    public function testFluentOptions(): void
    {
        $c = new TRestClient('http://127.0.0.1:1');
        $this->assertSame($c, $c->setTimeout(5));
        $this->assertSame($c, $c->setRetries(2, 1));
        $this->assertSame($c, $c->setBearerToken('abc'));
        $this->assertSame($c, $c->setBasicAuth('u', 'p'));
        $res = $c->get('/ping');
        $this->assertSame(0, $res['status']);
    }
}
