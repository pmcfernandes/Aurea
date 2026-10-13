<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TNIFValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TNIFValidator */
final class TNIFValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TNIFValidator();
        $v->validate('NIF', null);
        $v->validate('NIF', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        (new TNIFValidator())->validate('NIF', '123456789');
        $this->assertTrue(true);
    }

    public function testWrongCheckDigitThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TNIFValidator())->validate('NIF', '123456788');
    }

    public function testBadPrefixThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TNIFValidator())->validate('NIF', '423456789');
    }

    public function testBadLengthThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TNIFValidator())->validate('NIF', '12345678');
    }
}
