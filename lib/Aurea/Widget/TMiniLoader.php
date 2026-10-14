<?php
declare(strict_types=1);
namespace Aurea\Widget;
use Aurea\Core\TComponent;
/** Indicador compacto de carregamento acessível. */
class TMiniLoader extends TComponent { public function __construct(string $label='A carregar…'){parent::__construct('span');$this->addClass('spinner-border spinner-border-sm');$this->setProperty('role','status');$this->setProperty('aria-label',$label);} public function getContents():string{return '<span'.$this->renderAttributes().'><span class="visually-hidden">A carregar…</span></span>';}}
