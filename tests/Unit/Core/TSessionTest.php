<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TSession;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TSession */
final class TSessionTest extends TestCase
{
    protected function tearDown(): void
    {
        TSession::free();
    }

    public function testSetGetHasClear(): void
    {
        TSession::setValue('k', 'v');
        $this->assertTrue(TSession::has('k'));
        $this->assertSame('v', TSession::getValue('k'));
        TSession::clear('k');
        $this->assertFalse(TSession::has('k'));
        $this->assertSame('d', TSession::getValue('k', 'd'));
    }

    public function testFreeKeepsForeignKeys(): void
    {
        TSession::setValue('a', 1);
        $_SESSION['other_app'] = 'x';
        TSession::free();
        $this->assertFalse(TSession::has('a'));
        $this->assertSame('x', $_SESSION['other_app'] ?? null);
        unset($_SESSION['other_app']);
    }

    public function testRegenerateDoesNotFatal(): void
    {
        TSession::regenerate();
        $this->assertTrue(true);
    }
}
