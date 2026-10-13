<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TImageCropper — seleção e recorte de imagem via Cropper.js.
 *
 * O resultado do recorte é enviado no campo oculto `{nome}_data`
 * como data URL (preenchido no submit do formulário).
 *
 *   $crop = new TImageCropper('foto');
 *   $crop->setAspectRatio(1.0);
 *   $form->addContent($crop);
 *   // no servidor: TImageCropper::saveDataUrl($_POST['foto_data'], 'uploads');
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TImageCropper extends TComponent
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $cropId;
    protected string $name;
    protected ?float $aspectRatio = null;

    /**
     * Cria um recortador de imagem para o campo informado.
     *
     * @param string $name Nome do campo do ficheiro
     */
    public function __construct(string $name)
    {
        parent::__construct('div');
        self::$counter++;
        $this->cropId = 'cropper_' . self::$counter;
        $this->name = $name;
        $this->addClass('aurea-cropper');
    }

    /**
     * Define a proporção do recorte (null liberta o recorte).
     *
     * @param float|null $ratio Proporção largura/altura ou null para livre
     * @return static
     */
    public function setAspectRatio(?float $ratio): static
    {
        $this->aspectRatio = $ratio;
        return $this;
    }

    /**
     * Renderiza o input, a antevisão e o script do Cropper.js.
     *
     * @return string HTML do recortador
     */
    public function getContents(): string
    {
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">'
                . '<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>';
            self::$libIncluded = true;
        }
        $attrs = $this->renderAttributes();
        $name = htmlspecialchars($this->name, ENT_QUOTES);
        $id = $this->cropId;
        $ratio = $this->aspectRatio === null ? 'NaN' : (string) $this->aspectRatio;
        return "{$lib}<div{$attrs}>"
            . "<input type='file' class='form-control' id='{$id}_input' name='{$name}' accept='image/*'>"
            . "<img id='{$id}_img' style='max-width:100%;display:none;margin-top:.5rem'>"
            . "<input type='hidden' name='{$name}_data' id='{$id}_data'>"
            . '</div>'
            . "<script>(function(){var input=document.getElementById('{$id}_input');"
            . "var img=document.getElementById('{$id}_img');"
            . "var hidden=document.getElementById('{$id}_data');var cropper=null;"
            . "input.addEventListener('change',function(){var f=input.files&&input.files[0];if(!f){return;}"
            . "var r=new FileReader();r.onload=function(e){img.src=e.target.result;img.style.display='block';"
            . "if(cropper){cropper.destroy();}cropper=new Cropper(img,{aspectRatio:{$ratio},viewMode:1});};"
            . "r.readAsDataURL(f);});"
            . "var form=input.closest('form');if(form){form.addEventListener('submit',function(){"
            . "if(cropper){try{hidden.value=cropper.getCroppedCanvas().toDataURL();}catch(err){}}});}})();</script>";
    }

    /**
     * Grava uma data URL de imagem num diretório.
     *
     * @param string $dataUrl Data URL (data:image/...;base64,...)
     * @param string $targetDir Diretório de destino
     * @return string Caminho do ficheiro gravado
     * @throws \InvalidArgumentException Quando a data URL é inválida
     * @throws \RuntimeException Quando não é possível gravar o ficheiro
     */
    public static function saveDataUrl(string $dataUrl, string $targetDir): string
    {
        if (!preg_match('#^data:(image/(png|jpeg|jpg|gif|webp));base64,#', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Data URL de imagem inválida.');
        }
        $mime = $m[1] === 'image/jpg' ? 'image/jpeg' : $m[1];
        $raw = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($raw, true);
        if ($binary === false) {
            throw new \InvalidArgumentException('Data URL de imagem inválida.');
        }
        $ext = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mime];
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            throw new \RuntimeException("Não foi possível criar o diretório: {$targetDir}");
        }
        $path = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . uniqid('crop_', true) . '.' . $ext;
        if (file_put_contents($path, $binary) === false) {
            throw new \RuntimeException("Não foi possível gravar o ficheiro: {$path}");
        }
        return $path;
    }
}
