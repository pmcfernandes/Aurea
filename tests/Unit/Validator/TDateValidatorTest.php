<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TDateValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TDateValidator */
final class TDateValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TDateValidator();
        $v->validate('Data', null);
        $v->validate('Data', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        (new TDateValidator())->validate('Data', '25/12/2026');
        $this->assertTrue(true);
    }

    public function testCustomMaskPasses(): void
    {
        (new TDateValidator('yyyy-mm-dd'))->validate('Data', '2026-12-25');
        $this->assertTrue(true);
    }

    public function testInvalidDateThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TDateValidator())->validate('Data', '31/02/2026');
    }

    public function testWrongMaskThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TDateValidator())->validate('Data', '2026-12-25');
    }
}
