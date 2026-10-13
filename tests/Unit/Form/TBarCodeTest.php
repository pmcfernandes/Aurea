<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TBarCode;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TBarCode */
final class TBarCodeTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->sessionBackup = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_SESSION = $this->sessionBackup;
    }

    public function testGetContentsRendersSvgAndLegend(): void
    {
        $html = (new TBarCode('AB12'))->getContents();
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<rect', $html);
        $this->assertStringContainsString('<div>AB12</div>', $html);
        $this->assertStringContainsString("height='60'", $html);
    }

    public function testSetHeight(): void
    {
        $b = new TBarCode('A1');
        $this->assertSame($b, $b->setHeight(80));
        $this->assertStringContainsString("height='80'", $b->getContents());
    }

    public function testLowercaseIsUppercasedInLegend(): void
    {
        $html = (new TBarCode('ab'))->getContents();
        $this->assertStringContainsString('<div>AB</div>', $html);
    }

    public function testInvalidCharThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new TBarCode('A!'))->getContents();
    }
}
