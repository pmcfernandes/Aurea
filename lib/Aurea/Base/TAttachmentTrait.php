<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Database\TRecord;
use Aurea\Form\TUploader;

/**
 * Grava anexos em disco e persiste o respetivo caminho no Active Record.
 * O chamador deve manter a transação ativa e remover o ficheiro se reverter
 * uma operação posterior que dependa dele.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
trait TAttachmentTrait
{
    /**
     * @param TRecord $record Registo que receberá o caminho do anexo
     * @param string $field Campo em $_FILES
     * @param string $attribute Atributo persistido no registo
     * @param string $directory Diretório de destino privado
     * @param string[]|null $extensions Extensões permitidas
     * @param int $maxSize Limite em bytes
     * @param string[]|null $mimeTypes MIME types reais permitidos
     * @return string|null Caminho guardado ou null se não houver ficheiro
     */
    protected function storeAttachment(TRecord $record, string $field, string $attribute, string $directory, ?array $extensions = null, int $maxSize = 10485760, ?array $mimeTypes = null): ?string
    {
        $path = TUploader::save($field, $directory, $extensions, $maxSize, $mimeTypes);
        if ($path !== null) {
            $record->{$attribute} = $path;
            $record->store();
        }
        return $path;
    }
}
