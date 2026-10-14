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
    /** Constrói breadcrumb a partir de itens de menu já normalizados. */
    public static function fromMenu(array $items, string $activeAction): self
    {
        $trail = self::findTrail($items, $activeAction);
        return new self($trail ?: [['label' => 'Início', 'action' => null]]);
    }

    /** @return array<int,array{label:string,action:?TAction}> */
    private static function findTrail(array $items, string $activeAction, array $parents = []): array
    {
        foreach ($items as $item) {
            if (!is_array($item)) { continue; }
            $label = (string) ($item['label'] ?? '');
            $actionText = (string) ($item['action'] ?? '');
            $action = $actionText === '' ? null : new TAction([explode(':', $actionText, 2)[0], explode(':', $actionText, 2)[1] ?? 'onShow']);
            $current = [...$parents, ['label' => $label, 'action' => $action]];
            if ($actionText === $activeAction) { return $current; }
            $found = self::findTrail((array) ($item['children'] ?? []), $activeAction, $current);
            if ($found) { return $found; }
        }
        return [];
    }
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
