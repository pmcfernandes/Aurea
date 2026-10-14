<?php
declare(strict_types=1);

namespace Tests\Unit\Gen;

use PHPUnit\Framework\TestCase;

/** CLI Aurea */
final class TAureaCliTest extends TestCase
{
    public function testHelpListsCommands(): void
    {
        $out = (string) shell_exec('php ' . escapeshellarg('bin/aurea') . ' help');
        $this->assertStringContainsString('make:controller', $out);
        $this->assertStringContainsString('migrate', $out);
    }
}
