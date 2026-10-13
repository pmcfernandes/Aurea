<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TColor;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TColor */
final class TColorTest extends TestCase
{
    public function testTypeIsColor(): void
    {
        $f = new TColor('cor');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="color"', $html);
        $this->assertStringContainsString('name="cor"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new TColor('cor');
        $f->setValue('#ff0000');
        $this->assertSame('#ff0000', $f->getValue());
        $this->assertStringContainsString('value="#ff0000"', $f->getContents());
    }

    public function testLabelAndEditable(): void
    {
        $f = new TColor('cor');
        $f->setLabel('Cor')->setEditable(false);
        $this->assertSame('Cor', $f->getLabel());
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TColor('cor');
        $f->addValidation('Cor', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TColor('cor');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TColor('cor');
        $f->addValidation('Cor', new TRequiredValidator());
        $f->setValue('#ff0000');
        $f->validate();
        $this->assertTrue(true);
    }
}
