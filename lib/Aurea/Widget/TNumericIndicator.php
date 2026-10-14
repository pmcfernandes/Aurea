<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TNumericIndicator — indicador numérico semântico e formatável.
 *
 * Apresenta valores decimais ou monetários sem depender da extensão intl.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TNumericIndicator extends TComponent
{
    private float|int $value = 0;
    private int $decimals = 2;
    private string $decimalSeparator = ',';
    private string $thousandsSeparator = '.';
    private ?string $currency = null;

    /**
     * Cria o indicador com o valor inicial.
     *
     * @param float|int $value Valor a apresentar
     */
    public function __construct(float|int $value = 0)
    {
        parent::__construct('output');
        $this->addClass('aurea-numeric-indicator');
        $this->setValue($value);
    }

    /**
     * Define o valor apresentado.
     *
     * @param float|int $value Valor a apresentar
     * @return static
     */
    public function setValue(float|int $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Define a precisão e os separadores usados ao apresentar o número.
     *
     * @param int $decimals Casas decimais (zero ou mais)
     * @param string $decimalSeparator Separador decimal
     * @param string $thousandsSeparator Separador de milhares
     * @return static
     * @throws \InvalidArgumentException Para precisão negativa
     */
    public function setFormat(int $decimals = 2, string $decimalSeparator = ',', string $thousandsSeparator = '.'): static
    {
        if ($decimals < 0) {
            throw new \InvalidArgumentException('As casas decimais não podem ser negativas.');
        }
        $this->decimals = $decimals;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
        return $this;
    }

    /**
     * Acrescenta um código monetário, sem depender da extensão intl.
     *
     * @param string|null $currency Código ISO ou null para não o apresentar
     * @return static
     */
    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency === null ? null : strtoupper(trim($currency));
        return $this;
    }

    /**
     * Renderiza o elemento output com o valor humano e o atributo value bruto.
     *
     * @return string HTML do indicador
     */
    public function getContents(): string
    {
        $this->setProperty('value', (string) $this->value);
        $formatted = number_format((float) $this->value, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);
        if ($this->currency !== null && $this->currency !== '') {
            $formatted .= ' ' . htmlspecialchars($this->currency, ENT_QUOTES);
        }
        return '<output' . $this->renderAttributes() . '>' . $formatted . '</output>';
    }
}
