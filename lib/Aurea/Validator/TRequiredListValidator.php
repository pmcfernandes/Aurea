<?php
declare(strict_types=1);
namespace Aurea\Validator;
/** Exige uma quantidade mínima de valores numa coleção submetida. */
class TRequiredListValidator implements TValidatorInterface { public function __construct(private int $minimum=1){} public function validate(string $label,$value):void { if(!is_array($value)||count(array_filter($value,fn($v)=>$v!==null&&$v!==''))<$this->minimum) throw new \Exception("O campo {$label} requer pelo menos {$this->minimum} valor(es)."); } }
