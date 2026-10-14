<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TSession;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TSession */
final class TSessionFlashTest extends TestCase
{
    public function testFlashReadOnce(): void
    {
        TSession::setFlash('notice', 'olá');
        $this->assertSame('olá', TSession::getFlash('notice'));
        $this->assertNull(TSession::getFlash('notice'));
    }
}
