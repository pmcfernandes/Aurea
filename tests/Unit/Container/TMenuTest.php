<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Aurea\Container\TMenu;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Container\TMenu */
final class TMenuTest extends TestCase
{
    private string $tmpFile = '';

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        $this->tmpFile = '';
    }

    private function makeMenuFile(string $xml): string
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'menu') . '.xml';
        file_put_contents($this->tmpFile, $xml);
        return $this->tmpFile;
    }

    private function sampleXml(): string
    {
        return <<<XML
        <menu>
          <menuitem label="Cadastros" icon="fa-folder">
            <menu>
              <menuitem label="Clientes" icon="fa-users" action="ClienteList:onReload"/>
            </menu>
          </menuitem>
          <menuitem label="Início" icon="fa-house" action="HomeView"/>
        </menu>
        XML;
    }

    public function testFromXmlWithoutPermissions(): void
    {
        $menu = TMenu::fromXml($this->makeMenuFile($this->sampleXml()));
        $html = $menu->getContents();
        $this->assertStringContainsString('Cadastros', $html);
        $this->assertStringContainsString('Clientes', $html);
        $this->assertStringContainsString('Início', $html);
        $this->assertStringContainsString('dropdown-menu', $html);
    }

    public function testFromXmlWithPermissions(): void
    {
        $menu = TMenu::fromXml(
            $this->makeMenuFile($this->sampleXml()),
            fn(string $program): bool => $program !== 'ClienteList'
        );
        $html = $menu->getContents();
        $this->assertStringContainsString('Início', $html);
        $this->assertStringNotContainsString('Clientes', $html);
        $this->assertStringNotContainsString('Cadastros', $html);
    }

    public function testFromXmlMissingFileThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TMenu::fromXml(sys_get_temp_dir() . '/menu_inexistente_xyz.xml');
    }

    public function testFromXmlInvalidXmlThrows(): void
    {
        set_error_handler(static fn(): bool => true);
        try {
            TMenu::fromXml($this->makeMenuFile('isto não é xml <<<'));
            $this->fail('Era esperada uma RuntimeException para XML inválido.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('XML inválido', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }
}
