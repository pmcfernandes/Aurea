<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TMonthField;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TMonthField */
final class TMonthFieldTest extends TestCase
{
    public function testTypeIsMonth(): void
    {
        $f = new TMonthField('mes');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="month"', $html);
        $this->assertStringContainsString('name="mes"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new TMonthField('mes');
        $f->setValue('2026-01');
        $this->assertSame('2026-01', $f->getValue());
        $this->assertStringContainsString('value="2026-01"', $f->getContents());
    }

    public function testLabelAndEditable(): void
    {
        $f = new TMonthField('mes');
        $f->setLabel('Mês')->setEditable(false);
        $this->assertSame('Mês', $f->getLabel());
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TMonthField('mes');
        $f->addValidation('Mês', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TMonthField('mes');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TMonthField('mes');
        $f->addValidation('Mês', new TRequiredValidator());
        $f->setValue('2026-01');
        $f->validate();
        $this->assertTrue(true);
    }
}
