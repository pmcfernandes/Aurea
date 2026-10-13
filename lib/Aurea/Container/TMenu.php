<?php
declare(strict_types=1);

namespace Aurea\Container;

use Aurea\Core\TAction;
use Aurea\Core\TComponent;

/**
 * TMenu construído a partir de menu.xml.
 *
 *   <menu>
 *     <menuitem label="Cadastros" icon="fa-folder">
 *       <menu>
 *         <menuitem label="Clientes" icon="fa-users" action="ClienteList:onReload"/>
 *       </menu>
 *     </menuitem>
 *     <menuitem label="Início" icon="fa-house" action="HomeView"/>
 *   </menu>
 *
 * action = "Classe" (usa onShow) ou "Classe:metodo".
 * $allowed é um callback fn(string $programa): bool para permissões.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMenu extends TComponent
{
    /**
     * Cria a lista de navegação do menu.
     */
    public function __construct()
    {
        parent::__construct('ul');
        $this->addClass('navbar-nav me-auto');
    }

    /**
     * Constrói um menu a partir de um arquivo XML.
     *
     * @param string $file Caminho do arquivo menu.xml
     * @param callable|null $allowed Callback de permissão por programa
     * @return self TMenu construído
     * @throws \RuntimeException Se o arquivo for inválido
     */
    public static function fromXml(string $file, ?callable $allowed = null): self
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Menu não encontrado: {$file}");
        }
        $xml = simplexml_load_file($file);
        if (!$xml) {
            throw new \RuntimeException("XML inválido: {$file}");
        }
        $menu = new self;
        foreach ($xml->menuitem as $item) {
            $node = self::buildItem($item, $allowed);
            if ($node) {
                $menu->add($node);
            }
        }
        return $menu;
    }

    /**
     * Constrói um item do menu a partir do nó XML.
     *
     * @param \SimpleXMLElement $item Nó menuitem do XML
     * @param callable|null $allowed Callback de permissão por programa
     * @return ?TComponent Item construído ou null se filtrado
     */
    protected static function buildItem(\SimpleXMLElement $item, ?callable $allowed): ?TComponent
    {
        $label = (string) ($item['label'] ?? 'Item');
        $icon = (string) ($item['icon'] ?? 'fa-circle');
        $action = (string) ($item['action'] ?? '');
        $children = [];
        if (isset($item->menu->menuitem)) {
            foreach ($item->menu->menuitem as $sub) {
                $node = self::buildItem($sub, $allowed);
                if ($node) {
                    $children[] = $node;
                }
            }
        }
        if ($action !== '' && $allowed && !$allowed(self::programOf($action))) {
            return null;
        }
        if ($action === '' && !$children) {
            return null;
        }

        $li = new TComponent('li');
        if ($children) {
            $id = 'dd_' . substr(md5($label), 0, 8);
            $li->addClass('nav-item dropdown');
            $a = new TComponent('a');
            $a->addClass('nav-link dropdown-toggle');
            $a->setProperty('href', '#');
            $a->setProperty('role', 'button');
            $a->setProperty('data-bs-toggle', 'dropdown');
            $a->add("<i class='fa-solid {$icon}'></i> " . htmlspecialchars($label));
            $ul = new TComponent('ul');
            $ul->addClass('dropdown-menu');
            foreach ($children as $child) {
                $child->setProperty('class', trim(str_replace('nav-item', '', $child->getProperty('class') ?? '')));
                $ul->add($child);
            }
            $li->add($a);
            $li->add($ul);
            return $li;
        }

        $li->addClass('nav-item');
        $a = new TComponent('a');
        $a->addClass('nav-link');
        $a->setProperty('href', self::actionOf($action)->toHref());
        $a->add("<i class='fa-solid {$icon}'></i> " . htmlspecialchars($label));
        $li->add($a);
        return $li;
    }

    /**
     * Extrai o nome do programa da string de ação.
     *
     * @param string $action Ação no formato "Classe:metodo"
     * @return string Nome do programa
     */
    protected static function programOf(string $action): string
    {
        return explode(':', $action, 2)[0];
    }

    /**
     * Converte a string de ação num objeto Action.
     *
     * @param string $action Ação no formato "Classe:metodo"
     * @return TAction Ação construída
     */
    protected static function actionOf(string $action): TAction
    {
        $parts = explode(':', $action, 2);
        return new TAction([$parts[0], $parts[1] ?? 'onShow']);
    }
}
