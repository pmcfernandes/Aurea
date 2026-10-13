<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TMaxLengthValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TMaxLengthValidator */
final class TMaxLengthValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TMaxLengthValidator(3);
        $v->validate('Campo', null);
        $v->validate('Campo', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TMaxLengthValidator(3);
        $v->validate('Campo', 'abc');
        $v->validate('Campo', 'ab');
        $this->assertTrue(true);
    }

    public function testTooLongThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMaxLengthValidator(3))->validate('Campo', 'abcd');
    }
}
