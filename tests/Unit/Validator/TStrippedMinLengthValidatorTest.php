<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TStrippedMinLengthValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TStrippedMinLengthValidator */
final class TStrippedMinLengthValidatorTest extends TestCase
{
    public function testNullPasses(): void
    {
        (new TStrippedMinLengthValidator(5))->validate('Campo', null);
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TStrippedMinLengthValidator(5);
        $v->validate('Campo', 'Hello world');
        $v->validate('Campo', '<p>Hello world</p>');
        $this->assertTrue(true);
    }

    public function testTooShortThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TStrippedMinLengthValidator(5))->validate('Campo', 'Hi');
    }

    public function testHtmlTooShortThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TStrippedMinLengthValidator(5))->validate('Campo', '<b>Hi</b>');
    }
}
