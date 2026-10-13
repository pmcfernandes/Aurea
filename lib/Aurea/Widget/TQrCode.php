<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TQrCode — código QR gerado no navegador via qrcodejs.
 *
 *   $qr = new TQrCode('https://exemplo.pt');
 *   $qr->setSize(160);
 *   $qr->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TQrCode extends TComponent
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $qrId;
    protected string $text;
    protected int $size = 128;
    protected string $colorDark = '#000000';
    protected string $colorLight = '#ffffff';

    /**
     * Cria um código QR para o texto informado.
     *
     * @param string $text Texto ou URL codificado no QR
     */
    public function __construct(string $text)
    {
        parent::__construct('div');
        self::$counter++;
        $this->qrId = 'qrcode_' . self::$counter;
        $this->text = $text;
        $this->addClass('aurea-qrcode d-inline-block');
    }

    /**
     * Define o tamanho do código QR em pixels.
     *
     * @param int $px Largura e altura em pixels
     * @return static
     */
    public function setSize(int $px = 128): static
    {
        $this->size = $px;
        return $this;
    }

    /**
     * Define as cores do código QR.
     *
     * @param string $dark Cor dos módulos escuros
     * @param string $light Cor do fundo claro
     * @return static
     */
    public function setColors(string $dark, string $light): static
    {
        $this->colorDark = $dark;
        $this->colorLight = $light;
        return $this;
    }

    /**
     * Renderiza o contentor do QR e o script de inicialização.
     *
     * @return string HTML do código QR
     */
    public function getContents(): string
    {
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';
            self::$libIncluded = true;
        }
        $attrs = $this->renderAttributes();
        $text = json_encode($this->text);
        $dark = json_encode($this->colorDark);
        $light = json_encode($this->colorLight);
        return "{$lib}<div{$attrs}><div id='{$this->qrId}'></div></div>"
            . "<script>new QRCode(document.getElementById('{$this->qrId}'),"
            . "{text:{$text},width:{$this->size},height:{$this->size},colorDark:{$dark},colorLight:{$light}});</script>";
    }
}
