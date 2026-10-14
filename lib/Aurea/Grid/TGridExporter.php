<?php
declare(strict_types=1);

namespace Aurea\Grid;

/**
 * TGridExporter — exporta dados tabulares para CSV, Excel, XML, JSON e PDF.
 *
 * @author Pedro Fernandes
 * @version 1.1.0
 * @license MIT
 */
class TGridExporter
{
    protected static string $pdfTempDir = '';    /**
     * Exporta cabeçalhos e linhas para CSV descarregável.
     *
     * Envia os cabeçalhos Content-Type/Disposition (só se ainda não
     * foram enviados), escreve o BOM UTF-8 e as linhas com fputcsv
     * diretamente para php://output.
     *
     * @param array $headers Lista de títulos das colunas
     * @param array $rows Lista de linhas (arrays numéricos ou assoc; usa array_values)
     * @param string $filename Nome do ficheiro para download
     * @param string $delimiter Delimitador de campos
     */
    public static function csv(array $headers, array $rows, string $filename = 'export.csv', string $delimiter = ';'): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, array_values($headers), $delimiter);
        foreach ($rows as $row) {
            fputcsv($out, array_values((array) $row), $delimiter);
        }
        fclose($out);
    }

    /**
     * Exporta cabeçalhos e linhas para Excel (tabela HTML).
     *
     * Envia o Content-Type application/vnd.ms-excel (só se ainda não
     * foi enviado) e imprime uma tabela HTML com os valores escapados
     * por htmlspecialchars.
     *
     * @param array $headers Lista de títulos das colunas
     * @param array $rows Lista de linhas (arrays numéricos ou assoc; usa array_values)
     * @param string $filename Nome do ficheiro para download
     */
    public static function excel(array $headers, array $rows, string $filename = 'export.xls'): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        echo '<table border="1"><thead><tr>';
        foreach (array_values($headers) as $head) {
            echo '<th>' . htmlspecialchars((string) $head, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach (array_values((array) $row) as $cell) {
                echo '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Exporta cabeçalhos e linhas para XML descarregável.
     *
     * @param array $headers Lista de títulos das colunas
     * @param array $rows Lista de linhas (arrays numéricos ou assoc; usa array_values)
     * @param string $filename Nome do ficheiro para download
     * @param string $root Nome do elemento raiz
     * @param string $item Nome do elemento de cada linha
     */
    public static function xml(array $headers, array $rows, string $filename = 'export.xml', string $root = 'rows', string $item = 'row'): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $rootEl = $xml->createElement($root);
        $xml->appendChild($rootEl);
        $keys = array_values($headers);
        foreach ($rows as $row) {
            $itemEl = $xml->createElement($item);
            foreach (array_values((array) $row) as $i => $cell) {
                $name = preg_replace('/[^A-Za-z0-9_]/', '_', (string) ($keys[$i] ?? ('col' . $i)));
                if ($name === '') {
                    $name = 'col' . $i;
                }
                $child = $xml->createElement($name);
                $child->appendChild($xml->createTextNode((string) $cell));
                $itemEl->appendChild($child);
            }
            $rootEl->appendChild($itemEl);
        }
        echo $xml->saveXML();
    }

    /**
     * Exporta cabeçalhos e linhas para JSON descarregável.
     *
     * @param array $headers Lista de títulos das colunas
     * @param array $rows Lista de linhas (arrays numéricos ou assoc; usa array_values)
     * @param string $filename Nome do ficheiro para download
     */
    public static function json(array $headers, array $rows, string $filename = 'export.json'): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        $keys = array_values($headers);
        $out = [];
        foreach ($rows as $row) {
            $assoc = [];
            foreach (array_values((array) $row) as $i => $cell) {
                $assoc[(string) ($keys[$i] ?? ('col' . $i))] = $cell;
            }
            $out[] = $assoc;
        }
        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Define o diretório temporário do mPDF (precisa de escrita).
     * Por defeito usa sys_get_temp_dir() . '/aurea-mpdf'.
     */
    public static function setPdfTempDir(string $dir): void
    {
        self::$pdfTempDir = $dir;
    }

    /**
     * Obtém o diretório temporário do mPDF (cria se necessário).
     *
     * @return string Caminho do diretório temporário
     */
    protected static function pdfTempDir(): string
    {
        $dir = self::$pdfTempDir !== '' ? self::$pdfTempDir : sys_get_temp_dir() . '/aurea-mpdf';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * Gera PDF real com mPDF e devolve os bytes (%PDF-...).
     *
     * @param array $headers Títulos das colunas
     * @param array $rows Linhas (arrays numéricos ou assoc; usa array_values)
     * @param string $title Título do relatório
     * @param array $options orientation P|L, format A4|A3|A5|Letter|Legal, fontSize, footer bool
     */
    public static function pdfString(array $headers, array $rows, string $title = 'Exportação', array $options = []): string
    {
        $orientation = strtoupper((string) ($options['orientation'] ?? 'P'));
        if ($orientation !== 'L' && $orientation !== 'P') {
            $orientation = 'P';
        }
        $format = (string) ($options['format'] ?? 'A4');
        $fontSize = (int) ($options['fontSize'] ?? 10);
        $withFooter = (bool) ($options['footer'] ?? true);
        $generatedAt = date('d/m/Y H:i');

        $heads = array_values($headers);
        $thead = '';
        foreach ($heads as $head) {
            $thead .= '<th>' . htmlspecialchars((string) $head, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach (array_values((array) $row) as $cell) {
                $tbody .= '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $tbody .= '</tr>';
        }
        if ($tbody === '') {
            $tbody = '<tr><td colspan="' . max(1, count($heads)) . '">Sem registos.</td></tr>';
        }

        $footerHtml = $withFooter
            ? '<htmlpagefooter name="aurea"><div class="footer">Página {PAGENO} de {nbpg} — Gerado em '
                . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</div></htmlpagefooter>'
            : '';
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
            . 'body{font-family:sans-serif;font-size:' . $fontSize . 'pt;color:#111}'
            . 'h1{font-size:16pt;margin:0 0 4px} p.meta{font-size:8pt;color:#666;margin:0 0 10px}'
            . 'table{border-collapse:collapse;width:100%}'
            . 'thead{display:table-header-group} tfoot{display:table-footer-group} tr{page-break-inside:avoid}'
            . 'th,td{border:1px solid #555;padding:5px;text-align:left}'
            . 'th{background-color:#eee;font-weight:bold}'
            . '.footer{font-size:8pt;color:#666;text-align:center;border-top:1px solid #999;padding-top:4px}'
            . '</style></head><body>'
            . $footerHtml
            . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '<p class="meta">Gerado em ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . ' — ' . count($rows) . ' registo(s)</p>'
            . '<table><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table>'
            . '</body></html>';

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'orientation' => $orientation,
            'tempDir' => self::pdfTempDir(),
        ]);
        $mpdf->SetTitle($title);
        if ($withFooter) {
            $mpdf->SetHTMLFooter('<div class="footer">Página {PAGENO} de {nbpg}</div>');
        }
        $mpdf->WriteHTML($html);
        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    /**
     * Exporta cabeçalhos e linhas como PDF descarregável (bytes %PDF-...).
     */
    public static function pdf(array $headers, array $rows, string $filename = 'export.pdf', string $title = 'Exportação', array $options = []): void
    {
        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }
        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        echo self::pdfString($headers, $rows, $title, $options);
    }

    /**
     * Devolve dados tabulares como array associativo (reuso em APIs).
     */
    public static function toAssociative(array $headers, array $rows): array
    {
        $keys = array_values($headers);
        $out = [];
        foreach ($rows as $row) {
            $assoc = [];
            foreach (array_values((array) $row) as $i => $cell) {
                $assoc[(string) ($keys[$i] ?? ('col' . $i))] = $cell;
            }
            $out[] = $assoc;
        }
        return $out;
    }
}
