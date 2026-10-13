<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TSlider;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSlider */
final class TSliderTest extends TestCase
{
    public function testTypeIsRange(): void
    {
        $f = new TSlider('nivel');
        $html = $f->getContents();
        $this->assertStringContainsString('type="range"', $html);
        $this->assertStringContainsString('name="nivel"', $html);
    }

    public function testSetMin(): void
    {
        $f = new TSlider('nivel');
        $this->assertSame($f, $f->setMin(1));
        $this->assertStringContainsString('min="1"', $f->getContents());
    }

    public function testSetMax(): void
    {
        $f = new TSlider('nivel');
        $this->assertSame($f, $f->setMax(10));
        $this->assertStringContainsString('max="10"', $f->getContents());
    }

    public function testSetStep(): void
    {
        $f = new TSlider('nivel');
        $this->assertSame($f, $f->setStep(0.5));
        $this->assertStringContainsString('step="0.5"', $f->getContents());
    }

    public function testRendersOutputWithValue(): void
    {
        $f = new TSlider('nivel');
        $f->setMin(1)->setMax(10)->setValue('5');
        $this->assertSame('5', $f->getValue());
        $html = $f->getContents();
        $this->assertStringContainsString("id='out_nivel'", $html);
        $this->assertStringContainsString('>5</output>', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TSlider('nivel');
        $f->addValidation('Nível', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TSlider('nivel');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TSlider('nivel');
        $f->addValidation('Nível', new TRequiredValidator());
        $f->setValue('5');
        $f->validate();
        $this->assertTrue(true);
    }
}
