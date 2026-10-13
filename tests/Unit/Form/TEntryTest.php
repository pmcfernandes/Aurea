<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TEntry;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TEntry */
final class TEntryTest extends TestCase
{
    public function testTypeIsText(): void
    {
        $f = new TEntry('nome');
        $this->assertStringContainsString('type="text"', $f->getContents());
    }

    public function testGetNameAndLabel(): void
    {
        $f = new TEntry('nome');
        $this->assertSame('nome', $f->getName());
        $this->assertSame('nome', $f->getLabel());
        $f->setLabel('Nome');
        $this->assertSame('Nome', $f->getLabel());
    }

    public function testSetAndGetValue(): void
    {
        $f = new TEntry('nome');
        $f->setValue('ana');
        $this->assertSame('ana', $f->getValue());
        $this->assertStringContainsString('value="ana"', $f->getContents());
    }

    public function testSetMask(): void
    {
        $f = new TEntry('nome');
        $this->assertSame($f, $f->setMask('000.000.000-00'));
        $this->assertStringContainsString('data-mask="000.000.000-00"', $f->getContents());
    }

    public function testSetMaxLength(): void
    {
        $f = new TEntry('nome');
        $this->assertSame($f, $f->setMaxLength(50));
        $this->assertStringContainsString('maxlength="50"', $f->getContents());
    }

    public function testSetSizeAndPlaceholder(): void
    {
        $f = new TEntry('nome');
        $f->setSize('300px')->setPlaceholder('Nome completo');
        $html = $f->getContents();
        $this->assertStringContainsString('style="width:300px"', $html);
        $this->assertStringContainsString('placeholder="Nome completo"', $html);
    }

    public function testSetEditableRendersDisabled(): void
    {
        $f = new TEntry('nome');
        $f->setEditable(false);
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TEntry('nome');
        $f->addValidation('Nome', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TEntry('nome');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TEntry('nome');
        $f->addValidation('Nome', new TRequiredValidator());
        $f->setValue('ana');
        $f->validate();
        $this->assertTrue(true);
    }
}
