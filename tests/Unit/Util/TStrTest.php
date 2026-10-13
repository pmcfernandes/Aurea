<?php
declare(strict_types=1);

namespace Tests\Unit\Util;

use Aurea\Util\TStr;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Util\TStr */
final class TStrTest extends TestCase
{
    public function testLimit(): void
    {
        $this->assertSame('Hello...', TStr::limit('Hello world', 5));
        $this->assertSame('Hi', TStr::limit('Hi', 5));
        $this->assertSame('Hello', TStr::limit('<b>Hello</b>', 10));
    }

    public function testSlug(): void
    {
        $this->assertSame('ola-mundo', TStr::slug('Olá Mundo!'));
        $this->assertSame('cliente-123', TStr::slug('Cliente 123'));
    }

    public function testOnlyNumbers(): void
    {
        $this->assertSame('12345', TStr::onlyNumbers('abc123-45'));
        $this->assertSame('', TStr::onlyNumbers('abc'));
    }

    public function testApplyMask(): void
    {
        $this->assertSame('123.456.789', TStr::applyMask('123456789', '###.###.###'));
        $this->assertSame('(12) 3456', TStr::applyMask('12ab3456', '(##) ####'));
    }
}
