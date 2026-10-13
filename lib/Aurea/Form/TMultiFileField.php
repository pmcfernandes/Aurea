<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * TMultiFileField — campo de upload de múltiplos ficheiros.
 *
 *   $fotos = new TMultiFileField('anexos');
 *   $fotos->setAllowedExtensions(['pdf', 'png', 'jpg']);
 *   // no onSave:
 *   $paths = TUploader::saveMultiple('anexos', 'app/uploads');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMultiFileField extends TField
{
    /** @var string[] */
    protected array $allowedExtensions = [];
    protected int $maxSize = 10 * 1024 * 1024; // 10 MB

    /**
     * Cria o campo de upload múltiplo de ficheiros.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'file');
        $this->setProperty('multiple', 'multiple');
        $this->setProperty('name', $name . '[]');
    }

    /**
     * Define as extensões permitidas para os ficheiros.
     *
     * @param string[] $extensions Lista de extensões (sem ponto)
     * @return static
     */
    public function setAllowedExtensions(array $extensions): static
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    /**
     * Obtém as extensões permitidas.
     *
     * @return string[] Extensões permitidas
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Define o tamanho máximo de cada ficheiro.
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
     * Obtém o tamanho máximo de cada ficheiro.
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
