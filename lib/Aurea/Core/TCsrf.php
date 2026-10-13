<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TCsrf — proteção anti-CSRF por token de sessão (opt-in nos formulários).
 *
 *   $form->enableCsrf(); // TForm::validate() passa a exigir o token
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCsrf
{
    public const FIELD = '_csrf';

    /**
     * Obtém o token da sessão (cria um se ainda não existir).
     *
     * @return string Token CSRF
     */
    public static function token(): string
    {
        $token = TSession::getValue('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            TSession::setValue('csrf_token', $token);
        }
        return $token;
    }

    /**
     * Devolve o input hidden com o token para incluir no formulário.
     *
     * @return string HTML do campo hidden
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="' . self::token() . '">';
    }

    /**
     * Confirma o token submetido (comparação em tempo constante).
     *
     * @param mixed $token Token recebido no POST
     * @return bool Verdadeiro se válido
     */
    public static function validate(mixed $token): bool
    {
        $session = TSession::getValue('csrf_token');
        return is_string($token) && is_string($session) && $session !== ''
            && hash_equals($session, $token);
    }

    /**
     * Gera um novo token (ex: após login).
     */
    public static function rotate(): void
    {
        TSession::setValue('csrf_token', bin2hex(random_bytes(32)));
    }
}
