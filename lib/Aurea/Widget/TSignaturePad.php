<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;
use Aurea\Form\TField;

/**
 * TSignaturePad — captura assinatura como imagem (canvas).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TSignaturePad extends TField
{
    /**
     * Obtém o HTML do bloco de assinatura (canvas + campo hidden).
     *
     * @return string HTML do signature pad
     */
    public function getContents(): string
    {
        $name = htmlspecialchars($this->getName(), ENT_QUOTES);
        $id = 'sig_' . preg_replace('/[^A-Za-z0-9_]/', '_', $this->getName());
        return '<div class="aurea-signature">'
            . '<canvas id="' . $id . '" width="400" height="150" style="border:1px solid #ccc;border-radius:4px;touch-action:none"></canvas>'
            . '<input type="hidden" name="' . $name . '" id="' . $id . '_data">'
            . '<div><button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById(\'' . $id . '\').getContext(\'2d\').clearRect(0,0,400,150);document.getElementById(\'' . $id . '_data\').value=\'\'">Limpar</button></div>'
            . '<script>(function(){var c=document.getElementById("' . $id . '");if(!c)return;var ctx=c.getContext("2d");var drawing=false;function pos(e){var r=c.getBoundingClientRect();var t=e.touches?e.touches[0]:e;return [t.clientX-r.left,t.clientY-r.top];}c.addEventListener("mousedown",function(e){drawing=true;var p=pos(e);ctx.beginPath();ctx.moveTo(p[0],p[1]);});c.addEventListener("mousemove",function(e){if(!drawing)return;var p=pos(e);ctx.lineTo(p[0],p[1]);ctx.stroke();document.getElementById("' . $id . '_data").value=c.toDataURL();});window.addEventListener("mouseup",function(){drawing=false;});})();</script>'
            . '</div>';
    }
}
