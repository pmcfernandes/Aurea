<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TEntry;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TField */
final class TFieldTest extends TestCase
{
    public function testGetName(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame('cliente', $f->getName());
    }

    public function testLabelDefaultsToName(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame('cliente', $f->getLabel());
    }

    public function testSetAndGetLabel(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame($f, $f->setLabel('Cliente'));
        $this->assertSame('Cliente', $f->getLabel());
    }

    public function testSetAndGetValue(): void
    {
        $f = new TEntry('cliente');
        $this->assertNull($f->getValue());
        $this->assertSame($f, $f->setValue('abc'));
        $this->assertSame('abc', $f->getValue());
    }

    public function testSetEditableRendersDisabled(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame($f, $f->setEditable(false));
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
        $enabled = new TEntry('cliente');
        $this->assertStringNotContainsString('disabled', $enabled->getContents());
    }

    public function testSetSizeAddsStyle(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame($f, $f->setSize('200px'));
        $this->assertStringContainsString('style="width:200px"', $f->getContents());
    }

    public function testSetPlaceholderRenders(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame($f, $f->setPlaceholder('Digite aqui'));
        $this->assertStringContainsString('placeholder="Digite aqui"', $f->getContents());
    }

    public function testRenderBaseAttributes(): void
    {
        $f = new TEntry('cliente');
        $f->setValue('x');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('name="cliente"', $html);
        $this->assertStringContainsString('id="field_cliente"', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('value="x"', $html);
    }

    public function testRenderEscapesValue(): void
    {
        $f = new TEntry('cliente');
        $f->setValue('a"b&c');
        $this->assertStringContainsString('value="a&quot;b&amp;c"', $f->getContents());
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TEntry('cliente');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TEntry('cliente');
        $this->assertSame($f, $f->addValidation('Cliente', new TRequiredValidator()));
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TEntry('cliente');
        $f->addValidation('Cliente', new TRequiredValidator());
        $f->setValue('dado');
        $f->validate();
        $this->assertTrue(true);
    }
}
