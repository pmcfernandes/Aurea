<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TRadioGroup;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TRadioGroup */
final class TRadioGroupTest extends TestCase
{
    public function testAddItemsRendersRadios(): void
    {
        $f = new TRadioGroup('sexo');
        $this->assertSame($f, $f->addItems(['m' => 'Masculino', 'f' => 'Feminino']));
        $html = $f->getContents();
        $this->assertStringContainsString("type='radio'", $html);
        $this->assertStringContainsString("name='sexo'", $html);
        $this->assertStringContainsString("value='m'", $html);
        $this->assertStringContainsString('Masculino', $html);
        $this->assertStringContainsString('Feminino', $html);
    }

    public function testSetValueChecksRadio(): void
    {
        $f = new TRadioGroup('sexo');
        $f->addItems(['m' => 'Masculino', 'f' => 'Feminino']);
        $f->setValue('f');
        $this->assertSame('f', $f->getValue());
        $html = $f->getContents();
        $this->assertStringContainsString("value='f' checked", $html);
        $this->assertStringNotContainsString("value='m' checked", $html);
    }

    public function testNoCheckedByDefault(): void
    {
        $f = new TRadioGroup('sexo');
        $f->addItems(['m' => 'Masculino']);
        $this->assertStringNotContainsString('checked', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TRadioGroup('sexo');
        $f->addValidation('Sexo', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TRadioGroup('sexo');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TRadioGroup('sexo');
        $f->addValidation('Sexo', new TRequiredValidator());
        $f->setValue('m');
        $f->validate();
        $this->assertTrue(true);
    }
}
