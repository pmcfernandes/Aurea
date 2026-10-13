<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TSpinner;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSpinner */
final class TSpinnerTest extends TestCase
{
    public function testTypeIsNumber(): void
    {
        $f = new TSpinner('qtd');
        $html = $f->getContents();
        $this->assertStringContainsString('type="number"', $html);
        $this->assertStringContainsString('name="qtd"', $html);
    }

    public function testSetMin(): void
    {
        $f = new TSpinner('qtd');
        $this->assertSame($f, $f->setMin(0));
        $this->assertStringContainsString('min="0"', $f->getContents());
    }

    public function testSetMax(): void
    {
        $f = new TSpinner('qtd');
        $this->assertSame($f, $f->setMax(100));
        $this->assertStringContainsString('max="100"', $f->getContents());
    }

    public function testSetStep(): void
    {
        $f = new TSpinner('qtd');
        $this->assertSame($f, $f->setStep(2));
        $this->assertStringContainsString('step="2"', $f->getContents());
    }

    public function testRendersSpinnerButtons(): void
    {
        $f = new TSpinner('qtd');
        $f->setValue('3');
        $html = $f->getContents();
        $this->assertStringContainsString("class='input-group spinner'", $html);
        $this->assertStringContainsString('stepDown()', $html);
        $this->assertStringContainsString('stepUp()', $html);
        $this->assertStringContainsString('value="3"', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TSpinner('qtd');
        $f->addValidation('Qtd', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TSpinner('qtd');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TSpinner('qtd');
        $f->addValidation('Qtd', new TRequiredValidator());
        $f->setValue('3');
        $f->validate();
        $this->assertTrue(true);
    }
}
