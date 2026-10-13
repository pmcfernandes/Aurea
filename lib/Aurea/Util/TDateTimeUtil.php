<?php
declare(strict_types=1);

namespace Aurea\Util;

/**
 * TDateTimeUtil — conversões de máscara de data/hora.
 *
 * Máscaras: yyyy mm dd hh mi ss  (ex: 'dd/mm/yyyy', 'yyyy-mm-dd hh:mi:ss')
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDateTimeUtil
{
    protected static array $tokens = ['yyyy' => 'Y', 'mm' => 'm', 'dd' => 'd', 'hh' => 'H', 'mi' => 'i', 'ss' => 's'];

    /**
     * Converte uma máscara Aurea para formato PHP de data.
     *
     * @param string $mask Máscara com tokens yyyy/mm/dd/hh/mi/ss
     * @return string Formato PHP correspondente
     */
    protected static function toPhpFormat(string $mask): string
    {
        // divide a máscara pelos tokens; só escapa o que está FORA dos tokens
        $parts = preg_split('/(yyyy|mm|dd|hh|mi|ss)/', $mask, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        foreach ($parts ?: [] as $part) {
            if (isset(self::$tokens[$part])) {
                $out .= self::$tokens[$part];
            } else {
                $out .= preg_replace('/[A-Za-z]/', '\\\\$0', $part) ?? $part;
            }
        }
        return $out;
    }

    /**
     * Converte $value da máscara $fromMask para $toMask.
     * Ex: convertToMask('25/12/2026', 'dd/mm/yyyy', 'yyyy-mm-dd') => '2026-12-25'
     */
    public static function convertToMask(string $value, string $fromMask, string $toMask): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $dt = \DateTime::createFromFormat(self::toPhpFormat($fromMask), $value);
        if (!$dt) {
            return null;
        }
        return $dt->format(self::toPhpFormat($toMask));
    }

    /**
     * Converte data ISO (yyyy-mm-dd) para portuguesa (dd/mm/yyyy).
     *
     * @param string $usDate Data no formato yyyy-mm-dd
     * @return ?string Data no formato dd/mm/yyyy ou null
     */
    public static function dateToPT(string $usDate): ?string
    {
        return self::convertToMask(substr($usDate, 0, 10), 'yyyy-mm-dd', 'dd/mm/yyyy');
    }

    /**
     * Converte data portuguesa (dd/mm/yyyy) para ISO.
     *
     * @param string $ptDate Data no formato dd/mm/yyyy
     * @return ?string Data no formato yyyy-mm-dd ou null
     */
    public static function dateToUs(string $ptDate): ?string
    {
        return self::convertToMask(substr($ptDate, 0, 10), 'dd/mm/yyyy', 'yyyy-mm-dd');
    }

    /**
     * Retorna a data atual na máscara indicada.
     *
     * @param string $mask Máscara de saída
     * @return string Data atual formatada
     */
    public static function today(string $mask = 'yyyy-mm-dd'): string
    {
        return (new \DateTime)->format(self::toPhpFormat($mask));
    }

    /**
     * Retorna a data e hora atuais na máscara indicada.
     *
     * @param string $mask Máscara de saída
     * @return string Data e hora atuais formatadas
     */
    public static function now(string $mask = 'yyyy-mm-dd hh:mi:ss'): string
    {
        return (new \DateTime)->format(self::toPhpFormat($mask));
    }

    /**
     * Soma dias a uma data na máscara indicada.
     *
     * @param string $date Data base
     * @param int $days Dias a somar (negativo subtrai)
     * @param string $mask Máscara da data
     * @return ?string Nova data ou null se inválida
     */
    public static function addDays(string $date, int $days, string $mask = 'yyyy-mm-dd'): ?string
    {
        $dt = \DateTime::createFromFormat(self::toPhpFormat($mask), $date);
        if (!$dt) {
            return null;
        }
        $dt->modify(($days >= 0 ? '+' : '') . $days . ' days');
        return $dt->format(self::toPhpFormat($mask));
    }

    /** Diferença em dias entre duas datas (mesma máscara) */

    public static function diffDays(string $from, string $to, string $mask = 'yyyy-mm-dd'): ?int
    {
        $a = \DateTime::createFromFormat(self::toPhpFormat($mask), $from);
        $b = \DateTime::createFromFormat(self::toPhpFormat($mask), $to);
        if (!$a || !$b) {
            return null;
        }
        return (int) $a->diff($b)->format('%r%a');
    }

    /**
     * Verifica se um valor respeita a máscara indicada.
     *
     * @param string $value Valor a validar
     * @param string $mask Máscara esperada
     * @return bool Verdadeiro se válido
     */
    public static function isValid(string $value, string $mask): bool
    {
        $dt = \DateTime::createFromFormat(self::toPhpFormat($mask), $value);
        return $dt && $dt->format(self::toPhpFormat($mask)) === $value;
    }
}
