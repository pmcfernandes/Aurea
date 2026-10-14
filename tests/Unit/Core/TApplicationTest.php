<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TApplication;
use Aurea\Core\TAssets;
use Aurea\Core\TComponent;
use PHPUnit\Framework\TestCase;

final class StubJsonComponentController
{
    /** @param array $param Requisição */
    public function onShow(array $param = []): TComponent
    {
        $c = new TComponent('div');
        $c->add('STUB-COMPONENT-HTML');
        return $c;
    }
}

/** @covers \Aurea\Core\TApplication */
final class TApplicationTest extends TestCase
{
    private array $getBackup = [];
    private array $postBackup = [];
    private array $serverBackup = [];

    protected function setUp(): void
    {
        $this->getBackup = $_GET;
        $this->postBackup = $_POST;
        $this->serverBackup = $_SERVER;
        $_GET = [];
        $_POST = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH'], $_SERVER['HTTP_ACCEPT']);
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;
        $_SERVER = $this->serverBackup;
        http_response_code(200);
        $this->setStatic('controlNamespace', 'App\\Control\\');
        $this->setStatic('defaultClass', 'HomeView');
        $this->setStatic('defaultMethod', 'onShow');
        $this->setStatic('title', 'Aurea Framework');
        $this->setStatic('menuHtml', null);
        TAssets::reset();
    }

    private function setStatic(string $prop, mixed $value): void
    {
        $ref = new \ReflectionProperty(TApplication::class, $prop);
        $ref->setAccessible(true);
        $ref->setValue(null, $value);
    }

    private function getStatic(string $prop): mixed
    {
        $ref = new \ReflectionProperty(TApplication::class, $prop);
        $ref->setAccessible(true);
        return $ref->getValue();
    }

    public function testSetTitle(): void
    {
        TApplication::setTitle('Minha App');
        $this->assertSame('Minha App', $this->getStatic('title'));

        ob_start();
        TApplication::run('HomeView', 'onShow', []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('<title>Minha App</title>', $out);
    }

    public function testLayoutRendersRegisteredAssets(): void
    {
        TAssets::addCss('/assets/app.css');
        TAssets::addJs('/assets/app.js');
        ob_start();
        TApplication::run('HomeView', 'onShow', []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('/assets/app.css?v=', $out);
        $this->assertStringContainsString('/assets/app.js?v=', $out);
    }

    public function testSetDefault(): void
    {
        TApplication::setDefault('HomeView', 'onShow');
        $this->assertSame('HomeView', $this->getStatic('defaultClass'));

        ob_start();
        TApplication::run(null, null, []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('Bem-vindo', $out);
    }

    public function testSetControlNamespace(): void
    {
        TApplication::setControlNamespace('App\\Control');
        $this->assertSame('App\\Control\\', $this->getStatic('controlNamespace'));

        $html = TApplication::dispatch('HomeView', 'onShow', []);
        $this->assertStringContainsString('Bem-vindo', $html);

        TApplication::setControlNamespace('Nope\\Missing\\');
        $this->expectException(\RuntimeException::class);
        TApplication::dispatch('HomeView', 'onShow', []);
    }

    public function testSetMenuHtml(): void
    {
        TApplication::setMenuHtml('<li>MENU-TESTE-123</li>');

        ob_start();
        TApplication::run('HomeView', 'onShow', []);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('MENU-TESTE-123', $out);
    }

    public function testSetMenuXmlWithTempFile(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'menu') . '.xml';
        file_put_contents($tmp, '<menu><menuitem label="Xml-Item-456" icon="fa-house" action="HomeView"/></menu>');
        try {
            TApplication::setMenuXml($tmp);
            $this->assertStringContainsString('Xml-Item-456', (string) $this->getStatic('menuHtml'));

            ob_start();
            TApplication::run('HomeView', 'onShow', []);
            $out = (string) ob_get_clean();
            $this->assertStringContainsString('Xml-Item-456', $out);
        } finally {
            @unlink($tmp);
        }
    }

    public function testDispatchHomeView(): void
    {
        $html = TApplication::dispatch('HomeView', 'onShow', []);
        $this->assertStringContainsString('Bem-vindo', $html);
    }

    public function testDispatchClienteList(): void
    {
        $html = TApplication::dispatch('ClienteList', 'onReload', []);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Ana Souza', $html);
    }

    public function testDispatchMissingClassThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TApplication::dispatch('NopeMissingXyz', 'onShow', []);
    }

    public function testDispatchJsonWithArrayResult(): void
    {
        $json = TApplication::dispatchJson('ClienteApi', 'onList', ['limit' => 1]);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertSame(1, $data['page']);
    }

    public function testDispatchJsonWithComponentResult(): void
    {
        TApplication::setControlNamespace('Tests\\Unit\\Core\\');
        $json = TApplication::dispatchJson('StubJsonComponentController', 'onShow', []);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('html', $data);
        $this->assertStringContainsString('STUB-COMPONENT-HTML', $data['html']);
    }

    public function testRunWithJsonFormat(): void
    {
        $_GET = ['class' => 'ClienteApi', 'method' => 'onList', 'format' => 'json', 'limit' => 1];

        ob_start();
        TApplication::run();
        $out = (string) ob_get_clean();

        $data = json_decode($out, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
    }

    public function testRunWithMissingClassReturns500AndJson(): void
    {
        $_GET = ['class' => 'NopeMissingXyz', 'method' => 'onShow', 'format' => 'json'];

        ob_start();
        TApplication::run();
        $out = (string) ob_get_clean();

        $this->assertSame(404, http_response_code());
        $data = json_decode($out, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame(404, $data['code'] ?? null);
    }

    public function testRunRendersFullTemplate(): void
    {
        $_GET = ['class' => 'HomeView', 'method' => 'onShow'];

        ob_start();
        TApplication::run();
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('<!DOCTYPE html>', $out);
        $this->assertStringContainsString('Bem-vindo', $out);
    }
}
