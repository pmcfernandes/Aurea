<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TRadioButton;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TRadioButton */
final class TRadioButtonTest extends TestCase
{
    public function testRendersRadio(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $html = $f->getContents();
        $this->assertStringContainsString("type='radio'", $html);
        $this->assertStringContainsString("name='sexo'", $html);
        $this->assertStringContainsString("value='m'", $html);
        $this->assertStringContainsString('Masculino', $html);
    }

    public function testLabelDefaultsToValue(): void
    {
        $f = new TRadioButton('sexo', 'm');
        $this->assertStringContainsString('> m</label>', $f->getContents());
    }

    public function testSetGroupValueChecksWhenMatching(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $this->assertSame($f, $f->setGroupValue('m'));
        $this->assertSame('m', $f->getValue());
        $this->assertStringContainsString("value='m' checked", $f->getContents());
    }

    public function testSetGroupValueUnchecksWhenDifferent(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $f->setGroupValue('f');
        $this->assertStringNotContainsString('checked', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $f->addValidation('Sexo', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TRadioButton('sexo', 'm', 'Masculino');
        $f->addValidation('Sexo', new TRequiredValidator());
        $f->setGroupValue('m');
        $f->validate();
        $this->assertTrue(true);
    }
}
