<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TCheckGroup;
use Aurea\Validator\TRequiredValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TCheckGroup */
final class TCheckGroupTest extends TestCase
{
    public function testAddItemsRendersCheckboxes(): void
    {
        $f = new TCheckGroup('cores');
        $this->assertSame($f, $f->addItems(['a' => 'Azul', 'v' => 'Verde']));
        $html = $f->getContents();
        $this->assertStringContainsString("type='checkbox'", $html);
        $this->assertStringContainsString("name='cores[]'", $html);
        $this->assertStringContainsString("value='a'", $html);
        $this->assertStringContainsString('Azul', $html);
        $this->assertStringContainsString('Verde', $html);
    }

    public function testSetValueChecksBoxes(): void
    {
        $f = new TCheckGroup('cores');
        $f->addItems(['a' => 'Azul', 'v' => 'Verde']);
        $this->assertSame($f, $f->setValue(['a']));
        $html = $f->getContents();
        $this->assertStringContainsString("value='a' checked", $html);
        $this->assertStringNotContainsString("value='v' checked", $html);
    }

    public function testGetValue(): void
    {
        $f = new TCheckGroup('cores');
        $f->setValue(['a', 'v']);
        $this->assertSame(['a', 'v'], $f->getValue());
    }

    public function testValidateThrowsWithRequiredValidator(): void
    {
        $f = new TCheckGroup('cores');
        $f->addValidation('Cores', new TRequiredValidator());
        $f->setValue([]);
        $this->expectException(\Exception::class);
        $f->validate();
    }

    public function testValidatePassesWithoutValidations(): void
    {
        $f = new TCheckGroup('cores');
        $f->setValue([]);
        $f->validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWithValue(): void
    {
        $f = new TCheckGroup('cores');
        $f->addValidation('Cores', new TRequiredValidator());
        $f->setValue(['a']);
        $f->validate();
        $this->assertTrue(true);
    }
}
