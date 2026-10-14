<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;
use Aurea\Validator\TValidatorInterface;


/**
 * TField — campo base de formulário.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
abstract class TField extends TComponent
{
    protected string $name;
    protected string $label = '';
    protected mixed $value = null;
    protected bool $editable = true;
    /** @var array{0:string,1:TValidatorInterface}[] */
    protected array $validations = [];
    protected ?string $validationError = null;

    /**
     * Cria o campo base com o nome informado.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct('input');
        $this->name = $name;
        $this->setProperty('name', $name);
        $this->setProperty('id', 'field_' . $name);
        $this->addClass('form-control');
    }

    /**
     * Obtém o nome do campo.
     *
     * @return string Nome do campo
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Define o rótulo do campo.
     *
     * @param string $label Rótulo a exibir
     * @return static
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Obtém o rótulo do campo.
     *
     * @return string Rótulo do campo
     */
    public function getLabel(): string
    {
        return $this->label ?: $this->name;
    }

    /**
     * Define um valor para o campo.
     *
     * @param mixed $value Valor a atribuir ao campo
     * @return static
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Obtém o valor atual do campo.
     *
     * @return mixed Valor atual do campo
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Define se o campo é editável.
     *
     * @param bool $editable Indica se o campo é editável
     * @return static
     */
    public function setEditable(bool $editable): static
    {
        $this->editable = $editable;
        return $this;
    }

    /**
     * Define a largura do campo.
     *
     * @param string $size Largura do campo
     * @return static
     */
    public function setSize(string $size): static
    {
        return $this->setStyle("width:{$size}");
    }

    /**
     * Define o texto de ajuda do campo.
     *
     * @param string $text Texto a exibir como ajuda
     * @return static
     */
    public function setPlaceholder(string $text): static
    {
        return $this->setProperty('placeholder', $text);
    }

    /**
     * Adiciona uma validação ao campo.
     *
     * @param string $label Rótulo usado na mensagem de erro
     * @param TValidatorInterface $validator Validador a aplicar
     * @return static
     */
    public function addValidation(string $label, TValidatorInterface $validator): static
    {
        $this->validations[] = [$label, $validator];
        return $this;
    }

    /**
     * Valida o valor do campo.
     *
     * @throws \Exception Quando alguma validação falha
     */
    public function validate(): void
    {
        $this->validationError = null;
        $classes = preg_split('/\s+/', (string) $this->getProperty('class')) ?: [];
        $this->setProperty('class', implode(' ', array_filter($classes, fn(string $class): bool => $class !== 'is-invalid')));
        $this->setProperty('aria-invalid', 'false');
        foreach ($this->validations as [$label, $validator]) {
            try {
                $validator->validate($label, $this->value);
            } catch (\Throwable $error) {
                $this->validationError = $error->getMessage();
                $this->addClass('is-invalid');
                $this->setProperty('aria-invalid', 'true');
                $this->setProperty('aria-describedby', $this->getProperty('id') . '_error');
                throw $error;
            }
        }
    }

    /**
     * Obtém a última mensagem de validação do campo.
     *
     * @return string|null Mensagem ou null quando não houve falha
     */
    public function getValidationError(): ?string
    {
        return $this->validationError;
    }

    /**
     * Sincroniza o valor com os atributos HTML.
     */
    protected function sync(): void
    {
        if ($this->value !== null && !is_array($this->value)) {
            $this->setProperty('value', (string) $this->value);
        }
        if (!$this->editable) {
            $this->setProperty('disabled', 'disabled');
        }
    }
}
