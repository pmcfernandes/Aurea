<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TNumericValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TNumericValidator */
final class TNumericValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TNumericValidator();
        $v->validate('Campo', null);
        $v->validate('Campo', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TNumericValidator();
        $v->validate('Campo', '123');
        $v->validate('Campo', 45);
        $v->validate('Campo', '12,5');
        $this->assertTrue(true);
    }

    public function testInvalidThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TNumericValidator())->validate('Campo', 'abc');
    }

    public function testAlphanumericThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TNumericValidator())->validate('Campo', '12a');
    }
}
