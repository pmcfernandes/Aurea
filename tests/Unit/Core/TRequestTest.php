<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TRequest;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TRequest */
final class TRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_X_REQUESTED_WITH'], $_SERVER['HTTP_ACCEPT']);
    }

    public function testAllMergesGetPost(): void
    {
        $_GET = ['a' => 1, 'b' => 'g'];
        $_POST = ['b' => 'p', 'c' => 3];
        $this->assertSame(['a' => 1, 'b' => 'p', 'c' => 3], TRequest::all());
        $this->assertSame('p', TRequest::get('b'));
        $this->assertSame('def', TRequest::get('missing', 'def'));
    }

    public function testIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(TRequest::isPost());
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(TRequest::isPost());
    }

    public function testIsAjax(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(TRequest::isAjax());
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $_GET = ['ajax' => 1];
        $this->assertTrue(TRequest::isAjax());
    }

    public function testWantsJson(): void
    {
        $this->assertFalse(TRequest::wantsJson());
        $_GET = ['format' => 'json'];
        $this->assertTrue(TRequest::wantsJson());
        $_GET = [];
        $_SERVER['HTTP_ACCEPT'] = 'text/html, application/json';
        $this->assertTrue(TRequest::wantsJson());
    }
}
