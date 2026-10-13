<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\THidden;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\THidden */
final class THiddenTest extends TestCase
{
    public function testTypeIsHidden(): void
    {
        $f = new THidden('id');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="id"', $html);
        $this->assertStringContainsString('id="field_id"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new THidden('id');
        $f->setValue('42');
        $this->assertSame('42', $f->getValue());
        $this->assertStringContainsString('value="42"', $f->getContents());
    }

    public function testLabelAccessors(): void
    {
        $f = new THidden('id');
        $this->assertSame('id', $f->getName());
        $this->assertSame('id', $f->getLabel());
        $f->setLabel('ID');
        $this->assertSame('ID', $f->getLabel());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new THidden('id');
        $f->addValidation('ID', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new THidden('id');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new THidden('id');
        $f->addValidation('ID', new TRequiredValidator());
        $f->setValue('42');
        $f->validate();
        $this->assertTrue(true);
    }
}
