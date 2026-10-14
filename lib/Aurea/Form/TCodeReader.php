<?php
declare(strict_types=1);

namespace Aurea\Form;

/**
 * Leitor de QR e códigos de barras através da API BarcodeDetector do browser.
 * O valor lido é submetido num input normal; browsers sem a API recebem uma
 * mensagem clara e podem manter o campo preenchido manualmente.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCodeReader extends TField
{
    private static int $counter = 0;
    private string $readerId;
    /** @var string[] */
    private array $formats = ['qr_code', 'code_128', 'ean_13'];

    /**
     * @param string $name Nome do input submetido com o valor lido
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        self::$counter++;
        $this->readerId = 'aurea-code-reader-' . self::$counter;
    }

    /**
     * Define os formatos aceites pela API BarcodeDetector.
     *
     * @param string[] $formats Identificadores de formato permitidos pelo browser
     * @return static
     * @throws \InvalidArgumentException Se não houver nenhum formato válido
     */
    public function setFormats(array $formats): static
    {
        $formats = array_values(array_filter($formats, fn(mixed $format): bool => is_string($format) && preg_match('/^[a-z0-9_]+$/', $format)));
        if ($formats === []) {
            throw new \InvalidArgumentException('Indique pelo menos um formato de código válido.');
        }
        $this->formats = $formats;
        return $this;
    }

    /**
     * Renderiza input manual, botão de câmara e o script de leitura progressiva.
     *
     * @return string HTML e JavaScript isolado para esta instância
     * @throws \JsonException Se os formatos não puderem ser serializados
     */
    public function getContents(): string
    {
        $id = $this->readerId;
        $inputId = $id . '-value';
        $formats = json_encode($this->formats, JSON_THROW_ON_ERROR);
        $name = htmlspecialchars($this->name, ENT_QUOTES);
        $value = htmlspecialchars((string) ($this->value ?? ''), ENT_QUOTES);
        $js = "(function(){const root=document.getElementById('{$id}'),video=root.querySelector('video'),input=document.getElementById('{$inputId}'),message=root.querySelector('[role=status]');let stream;root.querySelector('button').addEventListener('click',async function(){if(!('BarcodeDetector' in window)||!navigator.mediaDevices?.getUserMedia){message.textContent='Leitura por câmara não suportada neste browser.';return;}try{stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}}});video.srcObject=stream;await video.play();const detector=new BarcodeDetector({formats:{$formats}});const scan=async()=>{if(!stream)return;const codes=await detector.detect(video);if(codes[0]){input.value=codes[0].rawValue;input.dispatchEvent(new Event('change',{bubbles:true}));stream.getTracks().forEach(t=>t.stop());stream=null;message.textContent='Código lido.';return;}requestAnimationFrame(scan);};scan();}catch(e){message.textContent='Não foi possível aceder à câmara.';}});})();";
        return "<div id='{$id}' class='aurea-code-reader'><input id='{$inputId}' class='form-control' name='{$name}' value='{$value}'><button type='button' class='btn btn-outline-secondary mt-1'>Ler código</button><video class='d-block mt-2' muted playsinline></video><span class='visually-hidden' role='status' aria-live='polite'></span></div><script>{$js}</script>";
    }
}
