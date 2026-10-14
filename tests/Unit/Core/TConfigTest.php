<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TConfig;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TConfig */
final class TConfigTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/aurea_cfg_' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);
    }

    public function testPhpFile(): void
    {
        file_put_contents($this->dir . '/a.php', "<?php return ['db' => ['host' => 'h'], 'x' => 1];");
        $c = TConfig::load($this->dir . '/a.php');
        $this->assertSame('h', $c->get('db.host'));
        $this->assertSame(1, $c->get('x'));
        $this->assertSame('d', $c->get('missing', 'd'));
        $c->set('db.port', 5432);
        $this->assertSame(5432, $c->get('db.port'));
        $this->assertArrayHasKey('db', $c->all());
    }

    public function testIniFile(): void
    {
        file_put_contents($this->dir . '/a.ini', "[db]\nhost = h\nport = 5432\n");
        $c = TConfig::load($this->dir . '/a.ini');
        $this->assertSame('h', $c->get('db.host'));
    }

    public function testValidateAndCachedLoad(): void
    {
        $file = $this->dir . '/cached.php';
        file_put_contents($file, "<?php return ['name' => 'aurea', 'port' => 80, 'options' => []];");
        TConfig::clearCache();
        $one = TConfig::loadCached($file);
        $two = TConfig::loadCached($file);
        $this->assertSame($one, $two);
        $one->validate(['name' => 'string', 'port' => 'int', 'options' => 'array', 'missing' => 'nullable:string']);
        $this->expectException(\RuntimeException::class);
        $one->validate(['port' => 'string']);
    }

    public function testMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);
        TConfig::load($this->dir . '/nope.php');
    }
}
