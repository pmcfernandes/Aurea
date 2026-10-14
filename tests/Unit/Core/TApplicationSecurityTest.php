<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TApplication;
use Aurea\Core\TCsrf;
use Aurea\Core\TSession;
use Aurea\Auth\TPermission;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TApplication */
final class TApplicationSecurityTest extends TestCase
{
    protected function tearDown(): void
    {
        TApplication::setAuthCallback(null);
        TApplication::setDebug(false);
        TApplication::usePermissionGuard(false);
        TApplication::setCsrfProtection(false);
        TPermission::reset();
        TSession::free();
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    public function testEnvAndDebug(): void
    {
        TApplication::setEnv('prod');
        $this->assertFalse(TApplication::isDebug());
        $this->assertSame('prod', TApplication::getEnv());
        TApplication::setDebug(true);
        $this->assertTrue(TApplication::isDebug());
    }

    public function testAuthCallbackBlocks(): void
    {
        TApplication::setAuthCallback(fn() => false);
        $this->expectException(\RuntimeException::class);
        TApplication::dispatch('HomeView', 'onShow', []);
    }

    public function testErrorEnvelope(): void
    {
        TApplication::setDebug(false);
        $env = TApplication::errorEnvelope(new \RuntimeException('Página não encontrada: X'));
        $this->assertSame(404, $env['code']);
        $this->assertArrayNotHasKey('trace', $env);
        TApplication::setDebug(true);
        $env = TApplication::errorEnvelope(new \RuntimeException('boom'));
        $this->assertArrayHasKey('trace', $env);
    }

    public function testOnlyPublicEventMethodsCanBeDispatched(): void
    {
        $this->expectException(\RuntimeException::class);
        TApplication::dispatch('HomeView', 'getContents', []);
    }

    public function testPermissionGuardUsesControllerActionResource(): void
    {
        TPermission::grantAction('HomeView', 'onShow');
        TApplication::usePermissionGuard();
        $this->assertStringContainsString('Bem-vindo', TApplication::dispatch('HomeView', 'onShow', []));
        $this->expectException(\RuntimeException::class);
        TApplication::dispatch('ClienteList', 'onReload', []);
    }

    public function testCsrfProtectionRejectsMutableRequestWithoutToken(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        TApplication::setCsrfProtection();
        ob_start();
        TApplication::run('HomeView', 'onShow', []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('Token CSRF inválido', $out);

        $_SERVER['HTTP_X_CSRF_TOKEN'] = TCsrf::token();
        ob_start();
        TApplication::run('HomeView', 'onShow', []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('Bem-vindo', $out);
    }
}
