<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TTextArea;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TTextArea */
final class TTextAreaTest extends TestCase
{
    public function testRendersTextarea(): void
    {
        $f = new TTextArea('obs');
        $html = $f->getContents();
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="obs"', $html);
        $this->assertStringContainsString('rows="4"', $html);
    }

    public function testValueRendersInsideTags(): void
    {
        $f = new TTextArea('obs');
        $f->setValue('linha1');
        $this->assertSame('linha1', $f->getValue());
        $html = $f->getContents();
        $this->assertStringContainsString('>linha1</textarea>', $html);
    }

    public function testValueIsEscaped(): void
    {
        $f = new TTextArea('obs');
        $f->setValue('<b>&');
        $this->assertStringContainsString('&lt;b&gt;&amp;</textarea>', $f->getContents());
    }

    public function testSetSizeWidthOnly(): void
    {
        $f = new TTextArea('obs');
        $this->assertSame($f, $f->setSize('400px'));
        $this->assertStringContainsString('style="width:400px"', $f->getContents());
    }

    public function testSetSizeWithHeight(): void
    {
        $f = new TTextArea('obs');
        $f->setSize('400px', '120px');
        $html = $f->getContents();
        $this->assertStringContainsString('width:400px', $html);
        $this->assertStringContainsString('height:120px', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TTextArea('obs');
        $f->addValidation('Obs', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TTextArea('obs');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TTextArea('obs');
        $f->addValidation('Obs', new TRequiredValidator());
        $f->setValue('texto');
        $f->validate();
        $this->assertTrue(true);
    }
}
