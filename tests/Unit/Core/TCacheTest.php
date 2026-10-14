<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TArrayCache;
use Aurea\Core\TFileCache;
use PHPUnit\Framework\TestCase;

final class TCacheTest extends TestCase
{
    public function testArrayCacheSupportsValuesAndExpiry(): void
    {
        $cache = new TArrayCache;
        $cache->set('a', ['ok' => true]);
        $this->assertSame(['ok' => true], $cache->get('a'));
        $cache->set('expira', 'x', 1);
        sleep(1);
        $this->assertFalse($cache->has('expira'));
        $cache->delete('a');
        $this->assertNull($cache->get('a'));
    }

    public function testFileCachePersistsAndClears(): void
    {
        $dir = sys_get_temp_dir() . '/aurea_cache_' . uniqid();
        try {
            $cache = new TFileCache($dir);
            $cache->set('a/b', 'valor');
            $this->assertSame('valor', $cache->get('a/b'));
            $cache->clear();
            $this->assertFalse($cache->has('a/b'));
        } finally {
            foreach ((array) glob($dir . '/*') as $file) { @unlink($file); }
            @rmdir($dir);
        }
    }
}
