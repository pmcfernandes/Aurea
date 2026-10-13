<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TPostalCodeValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TPostalCodeValidator */
final class TPostalCodeValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TPostalCodeValidator();
        $v->validate('CP', null);
        $v->validate('CP', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        (new TPostalCodeValidator())->validate('CP', '1000-100');
        $this->assertTrue(true);
    }

    public function testWithoutDashThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TPostalCodeValidator())->validate('CP', '1000100');
    }

    public function testShortThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TPostalCodeValidator())->validate('CP', '100-100');
    }
}
