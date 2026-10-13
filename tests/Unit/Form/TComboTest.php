<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TCombo;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TCombo */
final class TComboTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $ref = new \ReflectionProperty(TCombo::class, 'searchLib');
        $ref->setValue(null, false);
    }

    public function testRendersSelectWithDefaultOption(): void
    {
        $f = new TCombo('estado');
        $html = $f->getContents();
        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="estado"', $html);
        $this->assertStringContainsString('<option value="">-- selecione --</option>', $html);
    }

    public function testAddItems(): void
    {
        $f = new TCombo('estado');
        $this->assertSame($f, $f->addItems(['sp' => 'São Paulo', 'rj' => 'Rio']));
        $html = $f->getContents();
        $this->assertStringContainsString('<option value="sp">São Paulo</option>', $html);
        $this->assertStringContainsString('<option value="rj">Rio</option>', $html);
    }

    public function testSelectedValue(): void
    {
        $f = new TCombo('estado');
        $f->addItems(['sp' => 'São Paulo', 'rj' => 'Rio']);
        $f->setValue('rj');
        $this->assertSame('rj', $f->getValue());
        $html = $f->getContents();
        $this->assertStringContainsString('<option value="rj" selected>', $html);
        $this->assertStringNotContainsString('<option value="sp" selected>', $html);
    }

    public function testEnableSearch(): void
    {
        $f = new TCombo('estado');
        $f->addItems(['sp' => 'São Paulo']);
        $this->assertSame($f, $f->enableSearch());
        $html = $f->getContents();
        $this->assertStringContainsString('TomSelect', $html);
        $this->assertStringContainsString("new TomSelect('#field_estado'", $html);
    }

    public function testEnableSearchIncludesLibOnce(): void
    {
        $f = new TCombo('estado');
        $f->enableSearch();
        $html = $f->getContents();
        $this->assertStringContainsString('tom-select', $html);
    }

    public function testDisableSearch(): void
    {
        $f = new TCombo('estado');
        $f->enableSearch(false);
        $this->assertStringNotContainsString('TomSelect', $f->getContents());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TCombo('estado');
        $f->addValidation('Estado', new TRequiredValidator());
        $f->setValue('');
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TCombo('estado');
        $f->setValue('');
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TCombo('estado');
        $f->addValidation('Estado', new TRequiredValidator());
        $f->setValue('sp');
        $f->validate();
        $this->assertTrue(true);
    }
}
