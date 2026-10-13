<?php
declare(strict_types=1);

namespace Tests\Unit\Chart;

use Aurea\Chart\TChart;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Chart\TChart */
final class TChartTest extends TestCase
{
    protected function setUp(): void
    {
        $prop = new \ReflectionProperty(TChart::class, 'libIncluded');
        $prop->setAccessible(true);
        $prop->setValue(null, false);
    }

    public function testInvalidTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TChart('invalido', 'T');
    }

    public function testSetLabels(): void
    {
        $chart = new TChart('bar', 'Vendas');
        $this->assertSame($chart, $chart->setLabels(['Jan', 'Fev', 'Mar']));
        $html = $chart->getContents();
        $this->assertStringContainsString('"labels":["Jan","Fev","Mar"]', $html);
        $this->assertStringContainsString('Vendas', $html);
    }

    public function testAddDataset(): void
    {
        $chart = new TChart('bar', 'Vendas');
        $chart->setLabels(['Jan', 'Fev']);
        $this->assertSame($chart, $chart->addDataset('2026', [10, 20]));
        $html = $chart->getContents();
        $this->assertStringContainsString('"label":"2026"', $html);
        $this->assertStringContainsString('"data":[10,20]', $html);
    }

    public function testAddDatasetCustomColor(): void
    {
        $chart = new TChart('line', 'T');
        $chart->addDataset('S', [1], '#ff0000');
        $html = $chart->getContents();
        $this->assertStringContainsString('#ff0000', $html);
    }

    public function testSetHeight(): void
    {
        $chart = new TChart('pie', 'Partes');
        $chart->setHeight(300);
        $this->assertSame('height:300px', $chart->getProperty('style'));
        $this->assertStringContainsString('height:300px', $chart->getContents());
    }

    public function testRendersLibraryOnce(): void
    {
        $chart = new TChart('bar', 'T');
        $first = $chart->getContents();
        $this->assertStringContainsString('chart.umd.min.js', $first);
        $second = (new TChart('bar', 'T'))->getContents();
        $this->assertStringNotContainsString('chart.umd.min.js', $second);
    }
}
