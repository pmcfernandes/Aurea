<?php
declare(strict_types=1);
namespace Aurea\Form;
/** Escala Likert acessível composta por opções radio. */
class TLikertScale extends TRadioGroup { public function getContents():string { $html=parent::getContents(); return str_replace("class='d-flex gap-3 flex-wrap'", "class='d-flex gap-3 flex-wrap' role='radiogroup' aria-label='".htmlspecialchars($this->getLabel(),ENT_QUOTES)."'", $html); } }
