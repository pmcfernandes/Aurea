<?php
declare(strict_types=1);

namespace Aurea\Database;

/**
 * Valida identificadores SQL fornecidos à API pública.
 *
 * Valores SQL devem sempre ser parâmetros. Identificadores não podem ser
 * parametrizados por PDO, por isso são limitados a nomes simples ou
 * qualificados (ex.: cliente.nome).
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TIdentifier
{
    /**
     * Valida e devolve um identificador simples ou qualificado.
     *
     * @throws \InvalidArgumentException Quando o valor contém SQL não permitido
     */
    public static function assert(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*$/', $identifier)) {
            throw new \InvalidArgumentException("Identificador SQL inválido: {$identifier}");
        }
        return $identifier;
    }
}
