<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use Aurea\Auth\THash;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Auth\THash */
final class THashTest extends TestCase
{
    public function testMakeReturnsBcryptHash(): void
    {
        $hash = THash::make('segredo');
        $this->assertNotSame('segredo', $hash);
        $this->assertNotEmpty($hash);
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function testVerify(): void
    {
        $hash = THash::make('segredo');
        $this->assertTrue(THash::verify('segredo', $hash));
        $this->assertFalse(THash::verify('errada', $hash));
    }

    public function testNeedsRehashFreshIsFalse(): void
    {
        $hash = THash::make('segredo');
        $this->assertFalse(THash::needsRehash($hash));
    }

    public function testNeedsRehashWhenBcryptPolicyChanges(): void
    {
        $hash = THash::make('segredo', ['cost' => 10]);

        $this->assertTrue(THash::needsRehash($hash));
        $this->assertFalse(THash::needsRehash($hash, ['cost' => 10]));
    }

    public function testInfo(): void
    {
        $hash = THash::make('segredo');
        $info = THash::info($hash);
        $this->assertIsArray($info);
        $this->assertSame('bcrypt', $info['algoName'] ?? null);
    }
}
