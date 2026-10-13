<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TTimeValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TTimeValidator */
final class TTimeValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TTimeValidator();
        $v->validate('Hora', null);
        $v->validate('Hora', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        (new TTimeValidator())->validate('Hora', '13:45');
        $this->assertTrue(true);
    }

    public function testCustomMaskPasses(): void
    {
        (new TTimeValidator('hh:mi:ss'))->validate('Hora', '13:45:30');
        $this->assertTrue(true);
    }

    public function testInvalidThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TTimeValidator())->validate('Hora', '25:00');
    }

    public function testGarbageThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TTimeValidator())->validate('Hora', 'abc');
    }
}
