<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TMaxValueValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TMaxValueValidator */
final class TMaxValueValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TMaxValueValidator(10);
        $v->validate('Campo', null);
        $v->validate('Campo', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TMaxValueValidator(10);
        $v->validate('Campo', 10);
        $v->validate('Campo', 5);
        $v->validate('Campo', '9,5');
        $this->assertTrue(true);
    }

    public function testAboveMaxThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMaxValueValidator(10))->validate('Campo', 11);
    }

    public function testNonNumericThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMaxValueValidator(10))->validate('Campo', 'abc');
    }
}
