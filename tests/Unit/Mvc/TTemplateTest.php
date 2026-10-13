<?php
declare(strict_types=1);

namespace Tests\Unit\Mvc;

use Aurea\Mvc\TTemplate;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Mvc\TTemplate */
final class TTemplateTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/aurea_tpl_' . uniqid() . '/';
        mkdir($this->dir);
        file_put_contents($this->dir . 'hello.php', '<h1><?= $title ?></h1>');
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '*'));
        rmdir($this->dir);
    }

    public function testAssignFetchDisplay(): void
    {
        $t = new TTemplate($this->dir);
        $t->assign('title', 'Olá');
        $this->assertSame('<h1>Olá</h1>', $t->fetch('hello.php'));
        ob_start();
        $t->display('hello.php');
        $this->assertSame('<h1>Olá</h1>', (string) ob_get_clean());
    }

    public function testSetVarsMergeAndClear(): void
    {
        $t = new TTemplate($this->dir);
        $t->setVars(['title' => 'A'])->setVars(['x' => 1]);
        $this->assertSame('<h1>A</h1>', $t->fetch('hello.php'));
        $t->setVars(['title' => 'B'], true);
        $this->assertSame('<h1>B</h1>', $t->fetch('hello.php'));
    }

    public function testSetPathFluent(): void
    {
        $t = new TTemplate;
        $this->assertSame($t, $t->setPath($this->dir));
        $t->assign('title', 'X');
        $this->assertSame('<h1>X</h1>', $t->fetch('hello.php'));
    }

    public function testMissingThrows(): void
    {
        $t = new TTemplate($this->dir);
        $this->expectException(\RuntimeException::class);
        $t->fetch('nope.php');
    }
}
