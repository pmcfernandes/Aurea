<?php
declare(strict_types=1);

namespace Aurea\Auth;

/**
 * TPermission — autorização por programa (whitelist de acessos).
 *
 * Modelo aberto: sem nenhuma regra registada, check() permite tudo.
 * Assim que existir pelo menos uma concessão, só programas concedidos
 * (globalmente ou ao utilizador) são permitidos.
 *
 *   TPermission::grant('ClienteList');          // global (todos)
 *   TPermission::grant('Admin', '1');           // só utilizador 1
 *   TPermission::setChecker(fn($p, $u) => ...); // regra externa (precedência)
 *   TMenu::fromXml($file, TPermission::menuFilter());
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TPermission
{
    /** @var array<string, array<string, bool>> programa => [userId|'*' => true] */
    protected static array $grants = [];
    /** @var callable|null fn(string $program, ?string $userId): bool */
    protected static $checker = null;

    /**
     * Concede acesso a um programa (global ou a um utilizador).
     *
     * @param string $program Nome do programa (classe do controller)
     * @param string|null $userId Id do utilizador ou null para global
     */
    public static function grant(string $program, ?string $userId = null): void
    {
        self::$grants[$program][$userId ?? '*'] = true;
    }

    /**
     * Define um verificador externo (tem precedência sobre as concessões).
     *
     * @param callable|null $fn Função fn(string $program, ?string $userId): bool ou null para remover
     */
    public static function setChecker(?callable $fn): void
    {
        self::$checker = $fn;
    }

    /**
     * Verifica se um programa é permitido (usa TAuth::id() se userId omisso).
     *
     * @param string $program Nome do programa a verificar
     * @param string|null $userId Id do utilizador ou null para o autenticado
     * @return bool True se o acesso é permitido
     */
    public static function check(string $program, ?string $userId = null): bool
    {
        $uid = $userId ?? TAuth::id();
        if (self::$checker !== null) {
            return (bool) (self::$checker)($program, $uid);
        }
        if (!self::$grants) {
            return true;
        }
        if (isset(self::$grants[$program]['*'])) {
            return true;
        }
        return $uid !== null && isset(self::$grants[$program][$uid]);
    }

    /**
     * Devolve um filtro de menu pronto para TMenu::fromXml().
     *
     * @return callable Função fn(string $program): bool
     */
    public static function menuFilter(): callable
    {
        return fn(string $program): bool => self::check($program);
    }

    /**
     * Limpa concessões e verificador (útil em testes).
     */
    public static function reset(): void
    {
        self::$grants = [];
        self::$checker = null;
    }
}
