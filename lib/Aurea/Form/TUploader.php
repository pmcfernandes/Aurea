<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * Gravação de ficheiros enviados no servidor.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TUploader
{
    /**
     * Move o upload para o diretório de destino e devolve o caminho final.
     *
     * @param string $field Nome do campo no vetor $_FILES
     * @param string $targetDir Diretório de destino
     * @param array|null $allowedExtensions Extensões permitidas
     * @param int $maxSize Tamanho máximo em bytes
     * @param string[]|null $allowedMimeTypes MIME types reais permitidos, detetados com finfo
     * @return string|null Caminho final do arquivo ou nulo
     * @throws \RuntimeException Quando o upload falha
     */
    public static function save(
        string $field,
        string $targetDir,
        ?array $allowedExtensions = null,
        int $maxSize = 10485760,
        ?array $allowedMimeTypes = null
    ): ?string
    {
        if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $info = $_FILES[$field];
        if (($info['error'] ?? 0) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Falha no upload (erro {$info['error']}).");
        }
        if ($info['size'] > $maxSize) {
            throw new \RuntimeException('Arquivo excede o tamanho máximo permitido.');
        }
        self::validateFile($info, $allowedExtensions, $allowedMimeTypes);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0750, true) && !is_dir($targetDir)) {
            throw new \RuntimeException("Não foi possível criar {$targetDir}.");
        }
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($info['name'])) ?? 'file';
        try {
            $prefix = bin2hex(random_bytes(16));
        } catch (\Throwable) {
            $prefix = uniqid('', true);
        }
        $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $prefix . '_' . $safe;
        if (!move_uploaded_file($info['tmp_name'], $dest)) {
            // em CLI/testes move_uploaded_file falha; tenta rename
            if (!rename($info['tmp_name'], $dest)) {
                throw new \RuntimeException('Não foi possível mover o arquivo enviado.');
            }
        }
        @chmod($dest, 0640);
        return $dest;
    }

    /**
     * Guarda uma imagem após confirmar o respetivo conteúdo.
     *
     * @param string[]|null $allowedExtensions
     * @param int $maxSize Tamanho máximo em bytes
     * @return string|null Caminho final da imagem
     * @throws \RuntimeException Quando o conteúdo não for uma imagem válida
     */
    public static function saveImage(
        string $field,
        string $targetDir,
        ?array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        int $maxSize = 10485760
    ): ?string {
        if (!empty($_FILES[$field]) && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $tmp = (string) ($_FILES[$field]['tmp_name'] ?? '');
            if ($tmp === '' || @getimagesize($tmp) === false) {
                throw new \RuntimeException('O ficheiro enviado não é uma imagem válida.');
            }
        }
        return self::save($field, $targetDir, $allowedExtensions, $maxSize, [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        ]);
    }

    /**
     * Move vários uploads para o diretório de destino e devolve os caminhos finais.
     *
     * @param string $field Nome do campo no vetor $_FILES (estrutura name[i])
     * @param string $targetDir Diretório de destino
     * @param array|null $allowedExtensions Extensões permitidas
     * @param int $maxSize Tamanho máximo em bytes por ficheiro
     * @param string[]|null $allowedMimeTypes MIME types reais permitidos
     * @return string[] Lista de caminhos finais dos ficheiros ([] se não houver nenhum)
     * @throws \RuntimeException Quando algum upload falha
     */
    public static function saveMultiple(
        string $field,
        string $targetDir,
        ?array $allowedExtensions = null,
        int $maxSize = 10485760,
        ?array $allowedMimeTypes = null
    ): array
    {
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'] ?? null)) {
            return [];
        }
        $info = $_FILES[$field];
        $total = count($info['name']);
        $destinations = [];
        for ($i = 0; $i < $total; $i++) {
            $error = $info['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $tmpKey = $field . '_tmp_' . $i;
            $_FILES[$tmpKey] = [
                'name' => $info['name'][$i],
                'type' => $info['type'][$i] ?? '',
                'tmp_name' => $info['tmp_name'][$i],
                'error' => $error,
                'size' => $info['size'][$i] ?? 0,
            ];
            try {
                $dest = self::save($tmpKey, $targetDir, $allowedExtensions, $maxSize, $allowedMimeTypes);
                if ($dest !== null) {
                    $destinations[] = $dest;
                }
            } finally {
                unset($_FILES[$tmpKey]);
            }
        }
        return $destinations;
    }

    /**
     * Valida extensão e MIME real do ficheiro temporário.
     *
     * @param array<string,mixed> $info Informação de $_FILES
     * @param string[]|null $allowedExtensions
     * @param string[]|null $allowedMimeTypes
     */
    private static function validateFile(array $info, ?array $allowedExtensions, ?array $allowedMimeTypes): void
    {
        $ext = strtolower(pathinfo((string) ($info['name'] ?? ''), PATHINFO_EXTENSION));
        if ($allowedExtensions && !in_array($ext, array_map('strtolower', $allowedExtensions), true)) {
            throw new \RuntimeException("Extensão .{$ext} não permitida.");
        }
        if (!$allowedMimeTypes) {
            return;
        }
        $tmp = (string) ($info['tmp_name'] ?? '');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $tmp !== '' ? $finfo->file($tmp) : false;
        $allowed = array_map('strtolower', $allowedMimeTypes);
        if ($mime === false || !in_array(strtolower($mime), $allowed, true)) {
            throw new \RuntimeException('Tipo de ficheiro não permitido.');
        }
    }
}
