<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TAction;
use Aurea\Core\TScript;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TScript */
final class TScriptTest extends TestCase
{
    protected function setUp(): void
    {
        TScript::clear();
    }

    public function testCreateAndRender(): void
    {
        $this->assertFalse(TScript::hasPending());
        TScript::create('alert(1);');
        $this->assertTrue(TScript::hasPending());
        $html = TScript::render();
        $this->assertStringContainsString('<script>', $html);
        $this->assertStringContainsString('alert(1);', $html);
        $this->assertFalse(TScript::hasPending());
        $this->assertSame('', TScript::render());
    }

    public function testHelpers(): void
    {
        TScript::reload();
        TScript::redirect(new TAction(['X', 'onY']));
        TScript::closeModal('m1');
        TScript::showModal('m2');
        $html = TScript::render();
        $this->assertStringContainsString('location.reload', $html);
        $this->assertStringContainsString('window.location=', $html);
        $this->assertStringContainsString('m1', $html);
        $this->assertStringContainsString('m2', $html);
    }
}
