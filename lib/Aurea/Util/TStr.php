<?php
declare(strict_types=1);

namespace Aurea\Util;

/**
 * TStr — helpers de string.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TStr
{
    /**
     * Trunca um texto ao tamanho máximo com sufixo.
     *
     * @param string $text Texto original
     * @param int $max Tamanho máximo
     * @param string $suffix Sufixo de truncamento
     * @return string Texto limitado
     */
    public static function limit(string $text, int $max, string $suffix = '...'): string
    {
        $plain = strip_tags($text);
        return mb_strlen($plain) > $max ? mb_substr($plain, 0, $max) . $suffix : $plain;
    }

    protected static array $accents = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ý' => 'y', 'ÿ' => 'y',
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ç' => 'C', 'Ñ' => 'N',
    ];

    /**
     * Converte um texto em slug URL (minúsculo, hifenizado).
     *
     * @param string $text Texto original
     * @return string Slug gerado
     */
    public static function slug(string $text): string
    {
        $text = strtr($text, self::$accents);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = strtolower((string) $text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-');
    }

    /**
     * Extrai só os dígitos de um texto.
     *
     * @param string $text Texto original
     * @return string Só dígitos
     */
    public static function onlyNumbers(string $text): string
    {
        return preg_replace('/\D/', '', $text) ?? '';
    }

    /** Aplica máscara tipo '###.###.###-##' sobre dígitos */

    public static function applyMask(string $digits, string $mask): string
    {
        $digits = self::onlyNumbers($digits);
        $out = '';
        $i = 0;
        foreach (str_split($mask) as $ch) {
            $out .= $ch === '#' ? ($digits[$i++] ?? '') : $ch;
        }
        return $out;
    }
}
