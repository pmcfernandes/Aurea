<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TAssets;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TAssets */
final class TAssetsTest extends TestCase
{
    public function testVersionedAssets(): void
    {
        TAssets::reset();
        TAssets::setVersion('9.9.9');
        TAssets::setBasePath('');
        TAssets::addCss('/css/app.css');
        TAssets::addJs('/js/app.js');
        TAssets::addCss('/css/app.css');
        $css = TAssets::renderCss();
        $this->assertStringContainsString('/css/app.css?v=9.9.9', $css);
        $this->assertSame(1, substr_count($css, 'app.css'));
        $this->assertStringContainsString('/js/app.js?v=9.9.9', TAssets::renderJs());
        TAssets::reset();
    }
}
