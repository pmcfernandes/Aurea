<?php
declare(strict_types=1);

namespace Aurea\Util;

/**
 * TCsvImporter — lê ficheiros CSV e importa linha a linha.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCsvImporter
{
    /**
     * Lê um ficheiro CSV e devolve as linhas.
     *
     * Remove o BOM do início do ficheiro. Quando $header é true, a
     * primeira linha é usada como cabeçalho e cada linha é devolvida
     * como array associativo; caso contrário devolve arrays numéricos.
     *
     * @param string $file Caminho do ficheiro CSV
     * @param string $delimiter Delimitador de campos
     * @param bool $header Indica se a primeira linha é cabeçalho
     * @return array Lista de linhas do CSV
     */
    public static function parse(string $file, string $delimiter = ';', bool $header = true): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Ficheiro CSV não encontrado: {$file}");
        }
        $handle = fopen($file, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Não foi possível ler o ficheiro CSV: {$file}");
        }
        $rows = [];
        while (($row = fgetcsv($handle, null, $delimiter)) !== false) {
            if ($row === [null]) {
                continue;
            }
            $rows[] = array_map(fn($v) => $v === null ? '' : (string) $v, $row);
        }
        fclose($handle);
        if ($rows === []) {
            return [];
        }
        $rows[0][0] = self::stripBom($rows[0][0] ?? '');
        if (!$header) {
            return $rows;
        }
        $headers = array_shift($rows);
        $result = [];
        foreach ($rows as $row) {
            $row = array_slice(array_pad($row, count($headers), ''), 0, count($headers));
            $result[] = array_combine($headers, $row) ?: [];
        }
        return $result;
    }

    /**
     * Importa um CSV chamando uma função por cada linha.
     *
     * @param string $file Caminho do ficheiro CSV
     * @param callable $rowFn Função chamada por linha (fn(array $row))
     * @param string $delimiter Delimitador de campos
     * @param bool $header Indica se a primeira linha é cabeçalho
     * @return int Número de linhas processadas
     */
    public static function import(string $file, callable $rowFn, string $delimiter = ';', bool $header = true): int
    {
        $rows = self::parse($file, $delimiter, $header);
        $count = 0;
        foreach ($rows as $row) {
            $rowFn($row);
            $count++;
        }
        return $count;
    }

    /**
     * Remove a marca BOM do início de um texto.
     *
     * @param string $text Texto a limpar
     * @return string Texto sem BOM
     */
    protected static function stripBom(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            return substr($text, 3);
        }
        return $text;
    }
}
