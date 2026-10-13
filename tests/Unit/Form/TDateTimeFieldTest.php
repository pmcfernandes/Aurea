<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TDateTimeField;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TDateTimeField */
final class TDateTimeFieldTest extends TestCase
{
    public function testTypeIsDatetimeLocal(): void
    {
        $f = new TDateTimeField('agenda');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="datetime-local"', $html);
        $this->assertStringContainsString('name="agenda"', $html);
    }

    public function testDefaultDatabaseMask(): void
    {
        $f = new TDateTimeField('agenda');
        $this->assertSame('yyyy-mm-dd hh:ii:ss', $f->getDatabaseMask());
    }

    public function testSetAndGetDatabaseMask(): void
    {
        $f = new TDateTimeField('agenda');
        $this->assertSame($f, $f->setDatabaseMask('dd/mm/yyyy hh:ii'));
        $this->assertSame('dd/mm/yyyy hh:ii', $f->getDatabaseMask());
    }

    public function testSetAndGetValue(): void
    {
        $f = new TDateTimeField('agenda');
        $f->setValue('2026-01-02T10:30');
        $this->assertSame('2026-01-02T10:30', $f->getValue());
        $this->assertStringContainsString('value="2026-01-02T10:30"', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TDateTimeField('agenda');
        $f->addValidation('Agenda', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TDateTimeField('agenda');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TDateTimeField('agenda');
        $f->addValidation('Agenda', new TRequiredValidator());
        $f->setValue('2026-01-02T10:30');
        $f->validate();
        $this->assertTrue(true);
    }
}
