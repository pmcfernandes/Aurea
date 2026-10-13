<?php
declare(strict_types=1);

namespace Aurea\Grid;

/**
 * TGridExporter — exporta dados tabulares para CSV e Excel.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TGridExporter
{
    /**
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
}
