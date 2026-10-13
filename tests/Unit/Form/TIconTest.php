<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TIcon;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TIcon */
final class TIconTest extends TestCase
{
    public function testRendersTextInputWithPreview(): void
    {
        $f = new TIcon('icone');
        $html = $f->getContents();
        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('name="icone"', $html);
        $this->assertStringContainsString("id='iconprev_icone'", $html);
        $this->assertStringContainsString("id='list_icone'", $html);
    }

    public function testSetItems(): void
    {
        $f = new TIcon('icone');
        $this->assertSame($f, $f->setItems(['fa fa-home', 'fa fa-user']));
        $html = $f->getContents();
        $this->assertStringContainsString('<option value="fa fa-home">', $html);
        $this->assertStringContainsString('<option value="fa fa-user">', $html);
        $this->assertStringContainsString('<datalist', $html);
    }

    public function testValueUpdatesPreviewClass(): void
    {
        $f = new TIcon('icone');
        $f->setValue('fa fa-home');
        $this->assertSame('fa fa-home', $f->getValue());
        $this->assertStringContainsString("class='fa fa-home'", $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TIcon('icone');
        $f->addValidation('Ícone', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TIcon('icone');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TIcon('icone');
        $f->addValidation('Ícone', new TRequiredValidator());
        $f->setValue('fa fa-home');
        $f->validate();
        $this->assertTrue(true);
    }
}
