<?php
declare(strict_types=1);
namespace Aurea\Widget;
use Aurea\Core\TComponent;
/** Barra de estados em pills com semântica de navegação. */
class TPillBar extends TComponent { private array $items=[]; public function __construct(){parent::__construct('nav');$this->setProperty('aria-label','Estados');} public function add(string $label,bool $active=false):static{$this->items[]=[$label,$active];return $this;} public function getContents():string{$out="<ul class='nav nav-pills'>";foreach($this->items as [$label,$active]){$out.="<li class='nav-item'><span class='nav-link".($active?' active':'')."'>".htmlspecialchars($label).'</span></li>'; }return '<nav'.$this->renderAttributes().">{$out}</ul></nav>";} }
