<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TMinLengthValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TMinLengthValidator */
final class TMinLengthValidatorTest extends TestCase
{
    public function testNullPasses(): void
    {
        (new TMinLengthValidator(3))->validate('Campo', null);
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TMinLengthValidator(3);
        $v->validate('Campo', 'abc');
        $v->validate('Campo', 'abcdef');
        $this->assertTrue(true);
    }

    public function testTooShortThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMinLengthValidator(3))->validate('Campo', 'ab');
    }

    public function testEmptyStringThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TMinLengthValidator(3))->validate('Campo', '');
    }
}
