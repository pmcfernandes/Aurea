<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\THtmlEditor;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\THtmlEditor */
final class THtmlEditorTest extends TestCase
{
    public function testRendersToolbarAndTextarea(): void
    {
        $f = new THtmlEditor('conteudo');
        $html = $f->getContents();
        $this->assertStringContainsString("class='htmleditor-toolbar'", $html);
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="conteudo"', $html);
    }

    public function testToolbarButtons(): void
    {
        $f = new THtmlEditor('conteudo');
        $html = $f->getContents();
        $this->assertStringContainsString('>B</button>', $html);
        $this->assertStringContainsString('>I</button>', $html);
        $this->assertStringContainsString('>Link</button>', $html);
        $this->assertStringContainsString('>Lista</button>', $html);
    }

    public function testToolbarReferencesFieldId(): void
    {
        $f = new THtmlEditor('conteudo');
        $html = $f->getContents();
        $this->assertStringContainsString('field_conteudo', $html);
    }

    public function testValueRendersInsideTags(): void
    {
        $f = new THtmlEditor('conteudo');
        $f->setValue('<b>oi</b>');
        $this->assertSame('<b>oi</b>', $f->getValue());
        $this->assertStringContainsString('&lt;b&gt;oi&lt;/b&gt;</textarea>', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new THtmlEditor('conteudo');
        $f->addValidation('Conteúdo', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new THtmlEditor('conteudo');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new THtmlEditor('conteudo');
        $f->addValidation('Conteúdo', new TRequiredValidator());
        $f->setValue('<p>x</p>');
        $f->validate();
        $this->assertTrue(true);
    }
}
