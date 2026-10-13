<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use Aurea\Auth\TAuth;
use Aurea\Core\TAction;
use Aurea\Core\TScript;
use Aurea\Core\TSession;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Auth\TAuth */
final class TAuthTest extends TestCase
{
    protected function tearDown(): void
    {
        TSession::free();
        TScript::clear();
    }

    public function testInitiallyLoggedOut(): void
    {
        $this->assertFalse(TAuth::check());
        $this->assertNull(TAuth::id());
        $this->assertNull(TAuth::user());
    }

    public function testLoginSetsCheckIdUser(): void
    {
        TAuth::login('1', 'Administrador');
        $this->assertTrue(TAuth::check());
        $this->assertSame('1', TAuth::id());
        $this->assertSame('Administrador', TAuth::user());
    }

    public function testLogoutClearsSession(): void
    {
        TAuth::login('1', 'Administrador');
        TAuth::logout();
        $this->assertFalse(TAuth::check());
        $this->assertNull(TAuth::id());
        $this->assertNull(TAuth::user());
    }

    public function testRequireLoginWithLogin(): void
    {
        TAuth::login('7', 'Bia');
        $this->assertTrue(TAuth::requireLogin());
        $this->assertFalse(TScript::hasPending());
    }

    public function testRequireLoginWithoutLogin(): void
    {
        $this->assertFalse(TAuth::requireLogin(new TAction(['LoginForm', 'onShow'])));
        $this->assertTrue(TScript::hasPending());
    }
}
