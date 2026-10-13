<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TMinValueValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TMinValueValidator */
final class TMinValueValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TMinValueValidator(10);
        $v->validate('Campo', null);
        $v->validate('Campo', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TMinValueValidator(10);
        $v->validate('Campo', 10);
        $v->validate('Campo', 15);
        $v->validate('Campo', '12,5');
        $this->assertTrue(true);
    }

    public function testBelowMinThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMinValueValidator(10))->validate('Campo', 9);
    }

    public function testNonNumericThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMinValueValidator(10))->validate('Campo', 'abc');
    }
}
