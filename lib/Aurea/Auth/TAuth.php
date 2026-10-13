<?php
declare(strict_types=1);

namespace Aurea\Auth;

use Aurea\Core\TAction;
use Aurea\Core\TScript;
use Aurea\Core\TSession;

/**
 * TAuth — autenticação mínima baseada em sessão.
 *
 *   TAuth::login('1', 'Administrador');
 *   TAuth::requireLogin(new TAction(['LoginForm', 'onShow']));
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TAuth
{
    /**
     * Autentica o usuário na sessão.
     *
     * @param string $id Id do usuário
     * @param string $name Nome do usuário
     * @param array $extra Dados extras da sessão
     */
    public static function login(string $id, string $name, array $extra = []): void
    {
        TSession::regenerate();
        TSession::setValue('auth_id', $id);
        TSession::setValue('auth_name', $name);
        TSession::setValue('auth_extra', $extra);
    }

    /**
     * Encerra a sessão do usuário.
     */
    public static function logout(): void
    {
        TSession::free();
    }

    /**
     * Indica se há usuário autenticado.
     *
     * @return bool True se autenticado
     */
    public static function check(): bool
    {
        return TSession::has('auth_id');
    }

    /**
     * Obtém o id do usuário autenticado.
     *
     * @return string|null Id do usuário ou null se ausente
     */
    public static function id(): ?string
    {
        $id = TSession::getValue('auth_id');
        return $id !== null ? (string) $id : null;
    }

    /**
     * Obtém o nome do usuário autenticado.
     *
     * @return string|null Nome do usuário ou null se ausente
     */
    public static function user(): ?string
    {
        return TSession::getValue('auth_name');
    }

    /**
     * Exige login; se ausente, redireciona para o form de login.
     * @return bool true se autenticado
     */
    public static function requireLogin(?TAction $loginAction = null): bool
    {
        if (self::check()) {
            return true;
        }
        $action = $loginAction ?? new TAction(['LoginForm', 'onShow']);
        TScript::redirect($action);
        return false;
    }
}
