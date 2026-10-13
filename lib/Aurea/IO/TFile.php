<?php
declare(strict_types=1);

namespace Aurea\IO;

/**
 * TFile — utilitários de sistema de ficheiros.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFile
{
    /**
     * Envia um ficheiro para download.
     *
     * @param string $file Caminho do ficheiro
     * @param string|null $name Nome proposto (omissão = basename)
     * @return bool Verdadeiro se enviado, falso se inexistente
     */
    public static function download(string $file, ?string $name = null): bool
    {
        if (!is_file($file)) {
            return false;
        }
        if (!headers_sent()) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . addslashes($name ?? basename($file)) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
        }
        if (ob_get_level() > 0) {
            ob_clean();
        }
        flush();
        readfile($file);
        return true;
    }

    /**
     * Obtém a extensão do ficheiro.
     *
     * @param string $file Caminho com nome e extensão
     * @return string Extensão (sem ponto)
     */
    public static function getExtension(string $file): string
    {
        $parts = explode('.', $file);
        return (string) end($parts);
    }

    /**
     * Formata bytes em tamanho legível (B, KB, MB, GB).
     *
     * @param int $size Tamanho em bytes
     * @return string Tamanho formatado
     */
    public static function size(int $size): string
    {
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $length = (float) $size;
        $i = 0;
        while ($length >= 1024 && ($i + 1) < count($sizes)) {
            $i++;
            $length /= 1024;
        }
        return number_format($length, 2, ',', '.') . ' ' . $sizes[$i];
    }

    /**
     * Lê o conteúdo de um ficheiro.
     *
     * @param string $file Caminho do ficheiro
     * @return string Conteúdo
     * @throws \RuntimeException Se o ficheiro não existir
     */
    public static function read(string $file): string
    {
        if (!is_file($file)) {
            throw new \RuntimeException("Ficheiro não encontrado: {$file}");
        }
        return (string) file_get_contents($file);
    }

    /**
     * Escreve conteúdo num ficheiro (cria diretórios em falta).
     *
     * @param string $file Caminho do ficheiro
     * @param string $data Conteúdo
     * @return bool Verdadeiro se gravou
     */
    public static function write(string $file, string $data): bool
    {
        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            return false;
        }
        return file_put_contents($file, $data) !== false;
    }

    /**
     * Lista ficheiros e diretórios de um caminho (recursivo, sem . e ..).
     *
     * @param string $path Diretório a listar
     * @return string[] Caminhos encontrados
     */
    public static function getDir(string $path): array
    {
        $files = [];
        $flags = \FilesystemIterator::SKIP_DOTS;
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, $flags));
        foreach ($objects as $name => $object) {
            $files[] = $name;
        }
        return $files;
    }

    /**
     * Indica se o caminho é um diretório.
     *
     * @param string $fileOrDirectory Caminho a testar
     * @return bool Verdadeiro se for diretório
     */
    public static function isDir(string $fileOrDirectory): bool
    {
        return is_dir($fileOrDirectory);
    }

    /**
     * Indica se o ficheiro existe.
     *
     * @param string $file Caminho do ficheiro
     * @return bool Verdadeiro se existir
     */
    public static function exists(string $file): bool
    {
        return file_exists($file);
    }

    /**
     * Devolve informação do ficheiro (stat).
     *
     * @param string $file Caminho do ficheiro
     * @return array Dados do stat
     * @throws \RuntimeException Se o ficheiro não existir
     */
    public static function info(string $file): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Ficheiro não encontrado: {$file}");
        }
        $stat = stat($file);
        return is_array($stat) ? $stat : [];
    }

    /**
     * Cria um diretório.
     *
     * @param string $path Caminho a criar
     * @param bool $recursive Cria pais em falta
     * @return bool Verdadeiro se o diretório existe no fim
     */
    public static function createDirectory(string $path, bool $recursive = false): bool
    {
        if (is_dir($path)) {
            return true;
        }
        mkdir($path, 0777, $recursive);
        return is_dir($path);
    }

    /**
     * Apaga um ficheiro ou diretório (vazio).
     *
     * @param string $fileOrDirectory Caminho a apagar
     * @return bool Verdadeiro se deixou de existir
     */
    public static function delete(string $fileOrDirectory): bool
    {
        if (is_dir($fileOrDirectory)) {
            rmdir($fileOrDirectory);
        } elseif (file_exists($fileOrDirectory)) {
            unlink($fileOrDirectory);
        }
        return !file_exists($fileOrDirectory);
    }
}
