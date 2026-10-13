<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TPassword;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TPassword */
final class TPasswordTest extends TestCase
{
    public function testTypeIsPassword(): void
    {
        $f = new TPassword('senha');
        $html = $f->getContents();
        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="password"', $html);
        $this->assertStringContainsString('name="senha"', $html);
    }

    public function testSetAndGetValue(): void
    {
        $f = new TPassword('senha');
        $f->setValue('s3cret');
        $this->assertSame('s3cret', $f->getValue());
        $this->assertStringContainsString('value="s3cret"', $f->getContents());
    }

    public function testLabelAndEditable(): void
    {
        $f = new TPassword('senha');
        $this->assertSame('senha', $f->getLabel());
        $f->setLabel('Senha')->setEditable(false);
        $this->assertSame('Senha', $f->getLabel());
        $this->assertStringContainsString('disabled="disabled"', $f->getContents());
    }

    public function testSizeAndPlaceholder(): void
    {
        $f = new TPassword('senha');
        $f->setSize('250px')->setPlaceholder('Digite a senha');
        $html = $f->getContents();
        $this->assertStringContainsString('style="width:250px"', $html);
        $this->assertStringContainsString('placeholder="Digite a senha"', $html);
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TPassword('senha');
        $f->addValidation('Senha', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TPassword('senha');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TPassword('senha');
        $f->addValidation('Senha', new TRequiredValidator());
        $f->setValue('s3cret');
        $f->validate();
        $this->assertTrue(true);
    }
}
