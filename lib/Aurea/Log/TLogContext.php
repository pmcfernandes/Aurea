<?php
declare(strict_types=1);

namespace Aurea\Log;

/**
 * Contexto estruturado associado ao pedido atual.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TLogContext
{
    /** @var array<string,mixed> */
    private static array $values = [];

    /**
     * Inicia o contexto de um pedido e devolve o seu identificador de correlação.
     *
     * Usa `X-Request-ID` quando é válido; caso contrário cria um valor aleatório
     * e devolve-o também no cabeçalho da resposta.
     *
     * @param string|null $requestId Identificador de correlação opcional
     * @return string Identificador de correlação do pedido
     * @throws \Exception Se não for possível gerar bytes aleatórios
     */
    public static function beginRequest(?string $requestId = null): string
    {
        $candidate = $requestId ?? (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
        $id = preg_match('/^[A-Za-z0-9._-]{8,128}$/', $candidate)
            ? $candidate
            : bin2hex(random_bytes(16));

        self::$values = ['request_id' => $id];
        if (!headers_sent()) {
            header('X-Request-ID: ' . $id);
        }
        return $id;
    }

    /**
     * Define um valor de contexto para os registos seguintes.
     *
     * @param string $key Nome do campo de contexto
     * @param mixed $value Valor associado ao campo
     */
    public static function set(string $key, mixed $value): void
    {
        self::$values[$key] = $value;
    }

    /**
     * Obtém todo o contexto do pedido.
     *
     * @return array<string,mixed> Contexto estruturado atual
     */
    public static function all(): array
    {
        return self::$values;
    }

    /**
     * Limpa o contexto, útil para workers de longa duração e testes.
     */
    public static function clear(): void
    {
        self::$values = [];
    }
}
