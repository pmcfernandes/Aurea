<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TRequiredValidator */
final class TRequiredValidatorTest extends TestCase
{
    public function testValidPasses(): void
    {
        $v = new TRequiredValidator();
        $v->validate('Campo', 'valor');
        $v->validate('Campo', '0');
        $v->validate('Campo', 0);
        $v->validate('Campo', ['x']);
        $this->assertTrue(true);
    }

    public function testNullThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TRequiredValidator())->validate('Campo', null);
    }

    public function testEmptyStringThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TRequiredValidator())->validate('Campo', '');
    }

    public function testEmptyArrayThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TRequiredValidator())->validate('Campo', []);
    }
}
