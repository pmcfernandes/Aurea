<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use Aurea\Grid\TGridExporter;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TGridExporter */
final class TGridExporterFormatsTest extends TestCase
{
    public function testXml(): void
    {
        ob_start();
        TGridExporter::xml(['Nome', 'Idade'], [['Ana', 30]]);
        $out = (string) ob_get_clean();
        $this->assertStringContainsString('<Nome>Ana</Nome>', $out);
        $xml = simplexml_load_string($out);
        $this->assertNotFalse($xml);
    }

    public function testJson(): void
    {
        ob_start();
        TGridExporter::json(['Nome'], [['Ana']]);
        $out = (string) ob_get_clean();
        $data = json_decode($out, true);
        $this->assertSame([['Nome' => 'Ana']], $data);
    }

    public function testPdfGeneratesRealPdfBytes(): void
    {
        TGridExporter::setPdfTempDir(sys_get_temp_dir() . '/aurea-mpdf-tests');
        ob_start();
        TGridExporter::pdf(['Nome'], [['Ana']], 'rel.pdf', 'Relatório');
        $out = (string) ob_get_clean();
        $this->assertStringStartsWith('%PDF-', $out);
        $this->assertGreaterThan(1000, strlen($out));
    }

    public function testPdfStringWithOptions(): void
    {
        TGridExporter::setPdfTempDir(sys_get_temp_dir() . '/aurea-mpdf-tests');
        $rows = [];
        for ($i = 1; $i <= 60; $i++) {
            $rows[] = ["Linha {$i}", $i];
        }
        $pdf = TGridExporter::pdfString(['Nome', 'Nº'], $rows, 'Longo', ['orientation' => 'L', 'format' => 'A4']);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $empty = TGridExporter::pdfString(['Nome'], [], 'Vazio');
        $this->assertStringStartsWith('%PDF-', $empty);
    }

    public function testToAssociative(): void
    {
        $out = TGridExporter::toAssociative(['a', 'b'], [[1, 2]]);
        $this->assertSame([['a' => 1, 'b' => 2]], $out);
    }
}
