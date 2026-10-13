<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TStrippedMaxLengthValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TStrippedMaxLengthValidator */
final class TStrippedMaxLengthValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TStrippedMaxLengthValidator(5);
        $v->validate('Campo', null);
        $v->validate('Campo', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TStrippedMaxLengthValidator(5);
        $v->validate('Campo', 'Hi');
        $v->validate('Campo', '<b>Hi</b>');
        $this->assertTrue(true);
    }

    public function testTooLongThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TStrippedMaxLengthValidator(5))->validate('Campo', '123456');
    }

    public function testHtmlTooLongThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TStrippedMaxLengthValidator(5))->validate('Campo', '<b>Hello world</b>');
    }
}
