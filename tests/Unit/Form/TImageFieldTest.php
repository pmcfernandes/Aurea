<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TImageField;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TImageField */
final class TImageFieldTest extends TestCase
{
    public function testTypeIsFileWithImageAccept(): void
    {
        $f = new TImageField('foto');
        $html = $f->getContents();
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('accept="image/*"', $html);
        $this->assertStringContainsString('name="foto"', $html);
    }

    public function testRendersPreviewImg(): void
    {
        $f = new TImageField('foto');
        $html = $f->getContents();
        $this->assertStringContainsString("id='imgprev_foto'", $html);
        $this->assertStringContainsString("src=''", $html);
    }

    public function testSetPreviewUrl(): void
    {
        $f = new TImageField('foto');
        $this->assertSame($f, $f->setPreviewUrl('http://img/x.png'));
        $this->assertStringContainsString("src='http://img/x.png'", $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TImageField('foto');
        $f->addValidation('Foto', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TImageField('foto');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TImageField('foto');
        $f->addValidation('Foto', new TRequiredValidator());
        $f->setValue('foto.png');
        $f->validate();
        $this->assertTrue(true);
    }
}
