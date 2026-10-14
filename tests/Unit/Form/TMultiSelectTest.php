<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TMultiSelect;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TMultiSelect */
final class TMultiSelectTest extends TestCase
{
    public function testRendersAccessibleSelectedOptions(): void
    {
        $field = (new TMultiSelect('tags'))->addItems(['a' => 'Alfa', 'b' => 'Beta'])->setValue(['b'])->setVisibleOptions(3);
        $html = $field->getContents();
        $this->assertStringContainsString('name="tags[]"', $html);
        $this->assertStringContainsString('multiple="multiple"', $html);
        $this->assertStringContainsString('value="b" selected', $html);
        $this->assertStringContainsString('size="3"', $html);
    }
}
