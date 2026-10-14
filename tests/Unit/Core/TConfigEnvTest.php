<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TConfig;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TConfig */
final class TConfigEnvTest extends TestCase
{
    public function testFromArrayMergeRequire(): void
    {
        $c = TConfig::fromArray(['db' => ['host' => 'localhost']]);
        $this->assertSame('localhost', $c->get('db.host'));
        $c->merge(['db.port' => 5432, 'app.name' => 'Aurea']);
        $this->assertSame(5432, $c->get('db.port'));
        $c->require(['db.host', 'db.port']);
        $this->expectException(\RuntimeException::class);
        $c->require(['missing.key']);
    }

    public function testEnvAndLoadEnv(): void
    {
        $tmp = sys_get_temp_dir() . '/aurea_env_' . uniqid() . '.env';
        file_put_contents($tmp, "# comment\nFOO_TEST_AUREA=bar\nQUOTED=\"hello world\"\n");
        TConfig::loadEnv($tmp);
        $this->assertSame('bar', TConfig::env('FOO_TEST_AUREA'));
        $this->assertSame('hello world', TConfig::env('QUOTED'));
        unlink($tmp);
    }

    public function testLoadWithEnvOverlay(): void
    {
        $dir = sys_get_temp_dir() . '/aurea_cfg_' . uniqid();
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/app.php', "<?php return ['name' => 'base', 'debug' => false];");
        file_put_contents($dir . '/app.prod.php', "<?php return ['debug' => true];");
        $c = TConfig::loadWithEnv($dir . '/app.php', 'prod');
        $this->assertSame('base', $c->get('name'));
        $this->assertTrue($c->get('debug'));
    }
}
