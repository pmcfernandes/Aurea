<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc;

use Aurea\Mvc\TView;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Mvc\TView */
final class TViewTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/aurea_view_' . uniqid() . '/';
        mkdir($this->dir);
        file_put_contents($this->dir . 'card.php', '<p><?= $name ?>:<?= $n ?></p>');
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '*'));
        rmdir($this->dir);
    }

    public function testSetGetFetchRender(): void
    {
        $v = new TView('card', $this->dir);
        $v->set('name', 'Ana')->set('n', 3);
        $this->assertSame('Ana', $v->get('name'));
        $this->assertNull($v->get('missing'));
        $this->assertSame('<p>Ana:3</p>', $v->fetch());
        ob_start();
        $v->render();
        $this->assertSame('<p>Ana:3</p>', (string) ob_get_clean());
    }

    public function testDefaultBasePath(): void
    {
        $v = new TView('whatever');
        try {
            $v->fetch();
            $this->fail('devia lançar');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('whatever.php', $e->getMessage());
        }
    }
}
