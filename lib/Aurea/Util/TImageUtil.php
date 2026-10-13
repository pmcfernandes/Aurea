<?php
declare(strict_types=1);

namespace Aurea\Util;

/**
 * TImageUtil — helpers de imagem e ficheiros (miniatura, tamanho, nome seguro).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TImageUtil
{
    /**
     * Cria uma miniatura proporcional da imagem de origem.
     *
     * Sem a extensão gd, copia o original para o destino e devolve true.
     *
     * @param string $src Caminho da imagem de origem
     * @param string $dest Caminho da imagem de destino
     * @param int $maxW Largura máxima
     * @param int $maxH Altura máxima
     * @return bool True em caso de sucesso, false se a origem não existe
     */
    public static function thumbnail(string $src, string $dest, int $maxW, int $maxH): bool
    {
        if (!is_file($src)) {
            return false;
        }
        if (!function_exists('imagecreatetruecolor')) {
            return copy($src, $dest);
        }
        $info = @getimagesize($src);
        if ($info === false) {
            return false;
        }
        [$origW, $origH, $type] = $info;
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG => @imagecreatefrompng($src),
            IMAGETYPE_GIF => @imagecreatefromgif($src),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
            default => false,
        };
        if ($image === false) {
            return false;
        }
        $scale = min($maxW / max($origW, 1), $maxH / max($origH, 1), 1.0);
        $newW = max(1, (int) round($origW * $scale));
        $newH = max(1, (int) round($origH * $scale));
        $thumb = imagecreatetruecolor($newW, $newH);
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        $saved = match ($type) {
            IMAGETYPE_JPEG => @imagejpeg($thumb, $dest),
            IMAGETYPE_PNG => @imagepng($thumb, $dest),
            IMAGETYPE_GIF => @imagegif($thumb, $dest),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? @imagewebp($thumb, $dest) : false,
            default => false,
        };
        imagedestroy($image);
        imagedestroy($thumb);
        return (bool) $saved;
    }

    /**
     * Formata um tamanho em bytes para leitura humana.
     *
     * @param int $bytes Tamanho em bytes
     * @return string Tamanho formatado (B/KB/MB/GB com vírgula decimal)
     */
    public static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $unit = 'B';
        foreach ($units as $unit) {
            $value /= 1024;
            if ($value < 1024 || $unit === 'TB') {
                break;
            }
        }
        return number_format($value, 1, ',', '') . ' ' . $unit;
    }

    /**
     * Remove caracteres inseguros de um nome de ficheiro.
     *
     * @param string $name Nome original do ficheiro
     * @return string Nome só com A-Za-z0-9._- (resto substituído por _)
     */
    public static function sanitizeFilename(string $name): string
    {
        $base = basename($name);
        return preg_replace('/[^A-Za-z0-9._-]/', '_', $base) ?? 'ficheiro';
    }
}
