<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TCsrf;
use Aurea\Core\TSession;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TCsrf */
final class TCsrfTest extends TestCase
{
    protected function tearDown(): void
    {
        TSession::free();
    }

    public function testTokenStableAndField(): void
    {
        $t = TCsrf::token();
        $this->assertSame(64, strlen($t));
        $this->assertSame($t, TCsrf::token());
        $this->assertStringContainsString('name="_csrf"', TCsrf::field());
        $this->assertStringContainsString($t, TCsrf::field());
    }

    public function testValidateAndRotate(): void
    {
        $t = TCsrf::token();
        $this->assertTrue(TCsrf::validate($t));
        $this->assertFalse(TCsrf::validate('wrong'));
        $this->assertFalse(TCsrf::validate(null));
        TCsrf::rotate();
        $this->assertFalse(TCsrf::validate($t));
        $this->assertTrue(TCsrf::validate(TCsrf::token()));
    }
}
