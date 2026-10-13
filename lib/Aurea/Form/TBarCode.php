<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TComponent;


/**
 * TBarCode — código de barras Code 39 gerado em SVG puro.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TBarCode extends TComponent
{
    protected string $code;
    protected int $height = 60;
    protected int $narrow = 2;
    protected int $wide = 6;

    /** Padrões Code 39: 9 elementos barra/espaço, 1 = largo, 0 = estreito. */
    protected const PATTERNS = [
        '0' => '000110100', '1' => '100100001', '2' => '001100001',
        '3' => '101100000', '4' => '000110001', '5' => '100110000',
        '6' => '001110000', '7' => '000100101', '8' => '100100100',
        '9' => '001100100', 'A' => '100001001', 'B' => '001001001',
        'C' => '101001000', 'D' => '000011001', 'E' => '100011000',
        'F' => '001011000', 'G' => '000001101', 'H' => '100001100',
        'I' => '001001100', 'J' => '000011100', 'K' => '100000011',
        'L' => '001000011', 'M' => '101000010', 'N' => '000010011',
        'O' => '100010010', 'P' => '001010010', 'Q' => '000000111',
        'R' => '100000110', 'S' => '001000110', 'T' => '000010110',
        'U' => '110000001', 'V' => '011000001', 'W' => '111000000',
        'X' => '010010001', 'Y' => '110010000', 'Z' => '011010000',
        '-' => '010000101', '.' => '110000100', ' ' => '011000100',
        '*' => '010010100', '$' => '010101000', '/' => '010100010',
        '+' => '010001010', '%' => '000101010',
    ];

    /**
     * Cria o código de barras Code 39.
     *
     * @param string $code Texto a codificar
     */
    public function __construct(string $code)
    {
        parent::__construct('div');
        $this->code = strtoupper($code);
    }

    /**
     * Define a altura das barras.
     *
     * @param int $height Altura em píxeis
     * @return static
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Obtém o HTML do código de barras em SVG com a legenda.
     *
     * @return string SVG do código de barras com a legenda
     */
    public function getContents(): string
    {
        $text = '*' . $this->code . '*';
        $x = 10 * $this->narrow;
        $bars = '';
        foreach (str_split($text) as $ch) {
            if (!isset(self::PATTERNS[$ch])) {
                throw new \InvalidArgumentException("Caractere inválido no código de barras: {$ch}");
            }
            $pattern = self::PATTERNS[$ch];
            for ($i = 0; $i < 9; $i++) {
                $w = $pattern[$i] === '1' ? $this->wide : $this->narrow;
                if ($i % 2 === 0) {
                    $bars .= "<rect x='{$x}' y='0' width='{$w}' height='{$this->height}' fill='black'/>";
                }
                $x += $w;
            }
            $x += $this->narrow;
        }
        $width = $x + 10 * $this->narrow - $this->narrow;
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$this->height}'>"
             . "<rect x='0' y='0' width='{$width}' height='{$this->height}' fill='white'/>{$bars}</svg>";
        $legend = '<div>' . htmlspecialchars($this->code) . '</div>';
        return "<div class='barcode'>{$svg}{$legend}</div>";
    }
}
