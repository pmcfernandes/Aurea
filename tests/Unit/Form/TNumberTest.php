<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TNumber;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TNumber */
final class TNumberTest extends TestCase
{
    public function testTypeIsNumber(): void
    {
        $f = new TNumber('qtd');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="number"', $html);
        $this->assertStringContainsString('name="qtd"', $html);
    }

    public function testSetDecimalsZero(): void
    {
        $f = new TNumber('qtd');
        $this->assertSame($f, $f->setDecimals(0));
        $this->assertStringContainsString('step="1"', $f->getContents());
    }

    public function testSetDecimalsOne(): void
    {
        $f = new TNumber('qtd');
        $f->setDecimals(1);
        $this->assertStringContainsString('step="0.1"', $f->getContents());
    }

    public function testSetDecimalsTwo(): void
    {
        $f = new TNumber('qtd');
        $f->setDecimals(2);
        $this->assertStringContainsString('step="0.01"', $f->getContents());
    }

    public function testSetAndGetValue(): void
    {
        $f = new TNumber('qtd');
        $f->setValue('3.5');
        $this->assertSame('3.5', $f->getValue());
        $this->assertStringContainsString('value="3.5"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TNumber('qtd');
        $f->addValidation('Qtd', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TNumber('qtd');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TNumber('qtd');
        $f->addValidation('Qtd', new TRequiredValidator());
        $f->setValue('3.5');
        $f->validate();
        $this->assertTrue(true);
    }
}
