<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TTime;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TTime */
final class TTimeTest extends TestCase
{
    public function testTypeIsTime(): void
    {
        $f = new TTime('hora');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="time"', $html);
        $this->assertStringContainsString('name="hora"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new TTime('hora');
        $f->setValue('10:30');
        $this->assertSame('10:30', $f->getValue());
        $this->assertStringContainsString('value="10:30"', $f->getContents());
    }

    public function testLabelSizePlaceholderEditable(): void
    {
        $f = new TTime('hora');
        $f->setLabel('Hora')->setSize('120px')->setPlaceholder('hh:mm')->setEditable(false);
        $this->assertSame('Hora', $f->getLabel());
        $html = $f->getContents();
        $this->assertStringContainsString('style="width:120px"', $html);
        $this->assertStringContainsString('placeholder="hh:mm"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TTime('hora');
        $f->addValidation('Hora', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TTime('hora');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TTime('hora');
        $f->addValidation('Hora', new TRequiredValidator());
        $f->setValue('10:30');
        $f->validate();
        $this->assertTrue(true);
    }
}
