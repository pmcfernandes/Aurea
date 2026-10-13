<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TEmail;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TEmail */
final class TEmailTest extends TestCase
{
    public function testTypeIsEmail(): void
    {
        $f = new TEmail('email');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('name="email"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new TEmail('email');
        $f->setValue('a@b.com');
        $this->assertSame('a@b.com', $f->getValue());
        $this->assertStringContainsString('value="a@b.com"', $f->getContents());
    }

    public function testLabelSizePlaceholderEditable(): void
    {
        $f = new TEmail('email');
        $f->setLabel('E-mail')->setSize('300px')->setPlaceholder('a@b.com')->setEditable(false);
        $this->assertSame('E-mail', $f->getLabel());
        $html = $f->getContents();
        $this->assertStringContainsString('style="width:300px"', $html);
        $this->assertStringContainsString('placeholder="a@b.com"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TEmail('email');
        $f->addValidation('E-mail', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TEmail('email');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TEmail('email');
        $f->addValidation('E-mail', new TRequiredValidator());
        $f->setValue('a@b.com');
        $f->validate();
        $this->assertTrue(true);
    }
}
