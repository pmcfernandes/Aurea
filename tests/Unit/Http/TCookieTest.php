<?php
declare(strict_types=1);

namespace Tests\Unit\Http;

use Aurea\Http\TCookie;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Http\TCookie */
final class TCookieTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_COOKIE['tcookie_test']);
    }

    public function testHasGetDefault(): void
    {
        $this->assertFalse(TCookie::has('tcookie_test'));
        $this->assertNull(TCookie::get('tcookie_test'));
        $this->assertSame('d', TCookie::get('tcookie_test', 'd'));
        $_COOKIE['tcookie_test'] = 'abc';
        $this->assertTrue(TCookie::has('tcookie_test'));
        $this->assertSame('abc', TCookie::get('tcookie_test'));
    }

    public function testSetReturnsBool(): void
    {
        // em CLI não há cabeçalhos: devolve bool sem fatal
        $this->assertIsBool(TCookie::set('tcookie_test', 'v'));
        $this->assertIsBool(TCookie::set('tcookie_test', 'v', ['expires' => time() + 60, 'samesite' => 'Strict']));
    }

    public function testDelete(): void
    {
        $_COOKIE['tcookie_test'] = 'abc';
        $this->assertIsBool(TCookie::delete('tcookie_test'));
        $this->assertFalse(TCookie::has('tcookie_test'));
    }
}
