<?php
declare(strict_types=1);

namespace Tests\Unit\Util;

use Aurea\Util\TCsvImporter;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Util\TCsvImporter */
final class TCsvImporterTest extends TestCase
{
    private string $file = '';

    protected function tearDown(): void
    {
        if ($this->file !== '' && file_exists($this->file)) {
            unlink($this->file);
        }
    }

    private function makeCsv(string $content): string
    {
        $this->file = sys_get_temp_dir() . '/aurea_csv_' . uniqid() . '.csv';
        file_put_contents($this->file, $content);
        return $this->file;
    }

    public function testParseWithHeader(): void
    {
        $file = $this->makeCsv("nome;cidade\nAna;Porto\nBia;Lisboa\n");
        $rows = TCsvImporter::parse($file);
        $this->assertSame(
            [['nome' => 'Ana', 'cidade' => 'Porto'], ['nome' => 'Bia', 'cidade' => 'Lisboa']],
            $rows
        );
    }

    public function testParseWithoutHeader(): void
    {
        $file = $this->makeCsv("nome;cidade\nAna;Porto\n");
        $rows = TCsvImporter::parse($file, ';', false);
        $this->assertSame([['nome', 'cidade'], ['Ana', 'Porto']], $rows);
    }

    public function testParseStripsBom(): void
    {
        $file = $this->makeCsv("\xEF\xBB\xBFnome;cidade\nAna;Porto\n");
        $rows = TCsvImporter::parse($file);
        $this->assertSame([['nome' => 'Ana', 'cidade' => 'Porto']], $rows);
    }

    public function testParseMissingThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        TCsvImporter::parse(sys_get_temp_dir() . '/aurea_missing_' . uniqid() . '.csv');
    }

    public function testImport(): void
    {
        $file = $this->makeCsv("nome;cidade\nAna;Porto\nBia;Lisboa\n");
        $seen = [];
        $count = TCsvImporter::import($file, function (array $row) use (&$seen): void {
            $seen[] = $row;
        });
        $this->assertSame(2, $count);
        $this->assertSame('Ana', $seen[0]['nome']);
        $this->assertSame('Bia', $seen[1]['nome']);
    }
}
