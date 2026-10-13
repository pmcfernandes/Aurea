<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * Campo de ficheiro com pré-visualização de imagem.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TImageField extends TField
{
    protected ?string $previewUrl = null;

    /**
     * Cria o campo de imagem com antevisão.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'file');
        $this->setProperty('accept', 'image/*');
        $this->setProperty(
            'onchange',
            "var r=new FileReader();r.onload=function(e){document.getElementById('imgprev_{$name}').src=e.target.result};r.readAsDataURL(this.files[0]);"
        );
    }

    /**
     * Define a URL inicial da antevisão.
     *
     * @param string $url URL da imagem de antevisão
     * @return static
     */
    public function setPreviewUrl(string $url): static
    {
        $this->previewUrl = $url;
        return $this;
    }

    /**
     * Obtém o HTML do campo.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->sync();
        $input = parent::getContents();
        $src = htmlspecialchars($this->previewUrl ?? '', ENT_QUOTES);
        return $input . "<img id='imgprev_{$this->getName()}' src='{$src}'>";
    }
}
