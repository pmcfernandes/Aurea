<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TDate;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDate */
final class TDateTest extends TestCase
{
    public function testTypeIsDate(): void
    {
        $f = new TDate('nasc');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="date"', $html);
        $this->assertStringContainsString('name="nasc"', $html);
    }

    public function testSetMask(): void
    {
        $f = new TDate('nasc');
        $this->assertSame($f, $f->setMask('mm/dd/yyyy'));
        $ref = new \ReflectionProperty(TDate::class, 'mask');
        $this->assertSame('mm/dd/yyyy', $ref->getValue($f));
    }

    public function testSetDatabaseMask(): void
    {
        $f = new TDate('nasc');
        $this->assertSame($f, $f->setDatabaseMask('dd-mm-yyyy'));
        $ref = new \ReflectionProperty(TDate::class, 'dbMask');
        $this->assertSame('dd-mm-yyyy', $ref->getValue($f));
    }

    public function testSetAndGetValue(): void
    {
        $f = new TDate('nasc');
        $f->setValue('2026-01-02');
        $this->assertSame('2026-01-02', $f->getValue());
        $this->assertStringContainsString('value="2026-01-02"', $f->getContents());
    }

    public function testLabelAndEditable(): void
    {
        $f = new TDate('nasc');
        $f->setLabel('Nascimento')->setEditable(false);
        $this->assertSame('Nascimento', $f->getLabel());
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TDate('nasc');
        $f->addValidation('Nascimento', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TDate('nasc');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TDate('nasc');
        $f->addValidation('Nascimento', new TRequiredValidator());
        $f->setValue('2026-01-02');
        $f->validate();
        $this->assertTrue(true);
    }
}
