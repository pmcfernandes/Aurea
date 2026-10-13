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
     * @return string|null Caminho final do arquivo ou nulo
     * @throws \RuntimeException Quando o upload falha
     */
    public static function save(string $field, string $targetDir, ?array $allowedExtensions = null, int $maxSize = 10485760): ?string
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
        $ext = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
        if ($allowedExtensions && !in_array($ext, array_map('strtolower', $allowedExtensions), true)) {
            throw new \RuntimeException("Extensão .{$ext} não permitida.");
        }
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new \RuntimeException("Não foi possível criar {$targetDir}.");
        }
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($info['name'])) ?? 'file';
        $dest = rtrim($targetDir, '/') . '/' . uniqid() . '_' . $safe;
        if (!move_uploaded_file($info['tmp_name'], $dest)) {
            // em CLI/testes move_uploaded_file falha; tenta rename
            if (!rename($info['tmp_name'], $dest)) {
                throw new \RuntimeException('Não foi possível mover o arquivo enviado.');
            }
        }
        return $dest;
    }

    /**
     * Move vários uploads para o diretório de destino e devolve os caminhos finais.
     *
     * @param string $field Nome do campo no vetor $_FILES (estrutura name[i])
     * @param string $targetDir Diretório de destino
     * @param array|null $allowedExtensions Extensões permitidas
     * @param int $maxSize Tamanho máximo em bytes por ficheiro
     * @return string[] Lista de caminhos finais dos ficheiros ([] se não houver nenhum)
     * @throws \RuntimeException Quando algum upload falha
     */
    public static function saveMultiple(string $field, string $targetDir, ?array $allowedExtensions = null, int $maxSize = 10485760): array
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
                $dest = self::save($tmpKey, $targetDir, $allowedExtensions, $maxSize);
                if ($dest !== null) {
                    $destinations[] = $dest;
                }
            } finally {
                unset($_FILES[$tmpKey]);
            }
        }
        return $destinations;
    }
}
