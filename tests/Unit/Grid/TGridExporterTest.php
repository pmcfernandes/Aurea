<?php
declare(strict_types=1);

namespace Tests\Unit\Grid;

use Aurea\Grid\TGridExporter;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Grid\TGridExporter */
final class TGridExporterTest extends TestCase
{
    public function testCsvOutputsBomAndRows(): void
    {
        ob_start();
        TGridExporter::csv(['Nome', 'Cidade'], [['Ana', 'Sintra'], ['Bruno', 'POA']]);
        $out = (string) ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $out);
        $this->assertStringContainsString('Nome', $out);
        $this->assertStringContainsString('Ana', $out);
        $this->assertStringContainsString('POA', $out);
    }

    public function testCsvUsesAssociativeArrayValues(): void
    {
        ob_start();
        TGridExporter::csv(['Nome', 'Cidade'], [['nome' => 'Ana', 'cidade' => 'Sintra']]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('Ana;Sintra', str_replace('"', '', $out));
    }

    public function testCsvCustomDelimiter(): void
    {
        ob_start();
        TGridExporter::csv(['A', 'B'], [['1', '2']], 'x.csv', ',');
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('A,B', str_replace(["\r", "\n", '"'], '', $out));
    }

    public function testExcelOutputsTable(): void
    {
        ob_start();
        TGridExporter::excel(['Nome', 'Cidade'], [['Ana', 'Sintra']]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('<table', $out);
        $this->assertStringContainsString('<th>Nome</th>', $out);
        $this->assertStringContainsString('<td>Ana</td>', $out);
        $this->assertStringContainsString('</table>', $out);
    }

    public function testExcelEscapesHtml(): void
    {
        ob_start();
        TGridExporter::excel(['Nome'], [['<b>Ana</b>']]);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('&lt;b&gt;Ana&lt;/b&gt;', $out);
        $this->assertStringNotContainsString('<b>Ana</b>', $out);
    }
}
