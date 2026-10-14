<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TSelect;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TSelect */
final class TSelectTest extends TestCase
{
    public function testRendersOptions(): void
    {
        $s = new TSelect('cor');
        $s->addItems(['r' => 'Vermelho', 'b' => 'Azul']);
        $s->setValue('b');
        $html = $s->getContents();
        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('value="b" selected', $html);
        $this->assertSame(['r' => 'Vermelho', 'b' => 'Azul'], $s->getItems());
    }
}
