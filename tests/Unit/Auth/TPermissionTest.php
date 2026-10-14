<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use Aurea\Auth\TAuth;
use Aurea\Auth\TPermission;
use Aurea\Core\TSession;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Auth\TPermission */
final class TPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        TPermission::reset();
    }

    protected function tearDown(): void
    {
        TPermission::reset();
        TSession::free();
    }

    public function testOpenModelAllowsAll(): void
    {
        $this->assertTrue(TPermission::check('Anything'));
        $this->assertTrue(TPermission::check('Other', '99'));
    }

    public function testGrantGlobal(): void
    {
        TPermission::grant('ClienteList');
        $this->assertTrue(TPermission::check('ClienteList'));
        $this->assertFalse(TPermission::check('Admin'));
    }

    public function testGrantPerUser(): void
    {
        TPermission::grant('Admin', '1');
        $this->assertTrue(TPermission::check('Admin', '1'));
        $this->assertFalse(TPermission::check('Admin', '2'));
        $this->assertFalse(TPermission::check('Admin'));
    }

    public function testCheckUsesLoggedUser(): void
    {
        TPermission::grant('Admin', '1');
        TAuth::login('1', 'Admin');
        $this->assertTrue(TPermission::check('Admin'));
        TAuth::login('2', 'Outro');
        $this->assertFalse(TPermission::check('Admin'));
    }

    public function testSetCheckerOverrides(): void
    {
        TPermission::grant('ClienteList');
        TPermission::setChecker(fn(string $p, ?string $u): bool => false);
        $this->assertFalse(TPermission::check('ClienteList'));
        TPermission::setChecker(fn(string $p, ?string $u): bool => true);
        $this->assertTrue(TPermission::check('Nope'));
        TPermission::setChecker(null);
        $this->assertTrue(TPermission::check('ClienteList'));
        $this->assertFalse(TPermission::check('Nope'));
    }

    public function testMenuFilter(): void
    {
        TPermission::grant('ClienteList');
        $filter = TPermission::menuFilter();
        $this->assertIsCallable($filter);
        $this->assertTrue($filter('ClienteList'));
        $this->assertFalse($filter('Admin'));
    }

    public function testActionGrantAndChecker(): void
    {
        TPermission::grantAction('Cliente', 'onDelete', '1');
        $this->assertTrue(TPermission::checkAction('Cliente', 'onDelete', '1'));
        $this->assertFalse(TPermission::checkAction('Cliente', 'onDelete', '2'));
        TPermission::setActionChecker(fn(string $p, string $a, ?string $u): bool => $a === 'onShow');
        $this->assertTrue(TPermission::checkAction('Cliente', 'onShow'));
        $this->assertFalse(TPermission::checkAction('Cliente', 'onDelete'));
    }

    public function testReset(): void
    {
        TPermission::grant('X');
        TPermission::setChecker(fn(): bool => false);
        TPermission::reset();
        $this->assertTrue(TPermission::check('X'));
    }
}
