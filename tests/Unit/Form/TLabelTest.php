<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TLabel;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TLabel */
final class TLabelTest extends TestCase
{
    public function testRendersLabelTag(): void
    {
        $l = new TLabel('Nome:');
        $html = $l->getContents();
        $this->assertStringContainsString('<label', $html);
        $this->assertStringContainsString('Nome:', $html);
        $this->assertStringContainsString('</label>', $html);
    }

    public function testHasLabelClasses(): void
    {
        $l = new TLabel('Nome:');
        $html = $l->getContents();
        $this->assertStringContainsString('form-label', $html);
        $this->assertStringContainsString('fw-semibold', $html);
    }

    public function testRendersGivenText(): void
    {
        $l = new TLabel('E-mail do cliente');
        $this->assertSame('<label class="form-label fw-semibold">E-mail do cliente</label>', $l->getContents());
    }
}
