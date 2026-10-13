<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;


/**
 * Migalha de pão simples.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TBreadcrumb extends TComponent
{
    /**
     * @param array $trail [['label' => string, 'action' => ?TAction]]
     */
    public function __construct(array $trail)
    {
        parent::__construct('nav');
        $this->setProperty('aria-label', 'breadcrumb');
        $ol = new TComponent('ol');
        $ol->addClass('breadcrumb');
        $last = count($trail) - 1;
        foreach ($trail as $i => $step) {
            $li = new TComponent('li');
            $li->addClass('breadcrumb-item' . ($i === $last ? ' active' : ''));
            if ($i === $last || empty($step['action'])) {
                $li->add(htmlspecialchars($step['label']));
            } else {
                $a = new TComponent('a');
                $a->setProperty('href', $step['action']->toHref());
                $a->add(htmlspecialchars($step['label']));
                $li->add($a);
            }
            $ol->add($li);
        }
        $this->add($ol);
    }
}
