<?php
declare(strict_types=1);

namespace App\Lib;

use Aurea\Auth\TAuth;
use Aurea\Container\TMenu;
use Aurea\Core\TAction;
use Aurea\Core\TAssets;
use Aurea\Core\TScript;
use Aurea\Mvc\TView;

/** Layout principal autenticado do CRM. */
final class CrmShell
{
    private const PROJECT_NAME = 'Aurea CRM';
    private const VERSION = 'v1.0.0';

    /**
     * Compõe a página com sidebar e menu definido no XML da aplicação.
     *
     * @param array<string, string[]> $permissions
     */
    public static function render(string $content, string $active, array $permissions): string
    {
        if (!TAuth::check()) {
            return (new TView('layouts/guest'))
                ->set('title', self::PROJECT_NAME)
                ->set('content', $content)
                ->set('assetsCss', TAssets::renderCss())
                ->set('assetsJs', TAssets::renderJs())
                ->set('scripts', TScript::render())
                ->fetch();
        }

        TAssets::addCss('/assets/shell.css');
        TAssets::addJs('/assets/shell.js');
        $assetsCss = TAssets::renderCss();
        $assetsJs = TAssets::renderJs();
        $scripts = TScript::render();
        $menu = self::menu($active, $permissions);
        $userName = (string) (TAuth::user() ?: 'Utilizador');

        return (new TView('layouts/crm-shell'))
            ->set('title', self::PROJECT_NAME)
            ->set('version', self::VERSION)
            ->set('content', $content)
            ->set('menu', $menu)
            ->set('userName', $userName)
            ->set('userInitial', self::initial($userName))
            ->set('logoutUrl', (new TAction(['CrmLogin', 'onLogout']))->toHref())
            ->set('assetsCss', $assetsCss)
            ->set('assetsJs', $assetsJs)
            ->set('scripts', $scripts)
            ->fetch();
    }

    /** @param array<string, string[]> $permissions */
    private static function menu(string $active, array $permissions): ?TMenu
    {
        $file = APP_ROOT . '/app/menu.xml';
        if (!is_file($file)) {
            return null;
        }

        $menu = TMenu::fromXml(
            $file,
            static fn(string $controller): bool => $controller !== 'CrmLogin' && array_key_exists($controller, $permissions),
            $active . ':onShow'
        );
        $menu->addClass('crm-sidebar-menu');
        return $menu;
    }

    private static function initial(string $name): string
    {
        $initial = function_exists('mb_substr') ? mb_substr(trim($name), 0, 1) : substr(trim($name), 0, 1);
        return strtoupper($initial ?: 'U');
    }
}
