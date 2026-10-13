<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * Upload de arquivos.
 *
 *   $file = new TFileField('anexo');
 *   $file->setAllowedExtensions(['pdf', 'png', 'jpg']);
 *   // no onSave:
 *   $path = TUploader::save('anexo', 'app/uploads');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFileField extends TField
{
    /** @var string[] */
    protected array $allowedExtensions = [];
    protected int $maxSize = 10 * 1024 * 1024; // 10 MB

    /**
     * Cria o campo de upload de arquivo.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'file');
    }

    /** @param string[] $extensions */

    public function setAllowedExtensions(array $extensions): static
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    /**
     * Define o tamanho máximo do arquivo.
     *
     * @param int $bytes Tamanho máximo em bytes
     * @return static
     */
    public function setMaxSize(int $bytes): static
    {
        $this->maxSize = $bytes;
        return $this;
    }

    /**
     * Obtém as extensões permitidas.
     *
     * @return array Extensões permitidas
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Obtém o tamanho máximo do arquivo.
     *
     * @return int Tamanho máximo em bytes
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        return parent::getContents();
    }
}
