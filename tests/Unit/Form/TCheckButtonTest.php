<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TCheckButton;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TCheckButton */
final class TCheckButtonTest extends TestCase
{
    public function testConstructorWithLabel(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $this->assertSame('ativo', $f->getName());
        $this->assertSame('Ativo', $f->getLabel());
        $html = $f->getContents();
        $this->assertStringContainsString("type='checkbox'", $html);
        $this->assertStringContainsString("name='ativo'", $html);
        $this->assertStringContainsString('Ativo', $html);
    }

    public function testLabelDefaultsToName(): void
    {
        $f = new TCheckButton('ativo');
        $this->assertSame('ativo', $f->getLabel());
    }

    public function testSetValueTrueChecks(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $this->assertSame($f, $f->setValue(true));
        $this->assertSame('1', $f->getValue());
        $this->assertStringContainsString('checked', $f->getContents());
    }

    public function testSetValueFalseUnchecks(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $f->setValue(false);
        $this->assertNull($f->getValue());
        $this->assertStringNotContainsString('checked', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $f->addValidation('Ativo', new TRequiredValidator());
        $f->setValue(false);
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $f->setValue(false);
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TCheckButton('ativo', 'Ativo');
        $f->addValidation('Ativo', new TRequiredValidator());
        $f->setValue(true);
        $f->validate();
        $this->assertTrue(true);
    }
}
