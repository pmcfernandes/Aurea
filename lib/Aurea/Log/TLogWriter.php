<?php
declare(strict_types=1);

namespace Aurea\Log;

/**
 * Adaptador de TLog para APIs legadas baseadas em TLoggerInterface.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TLogWriter implements TLoggerInterface
{
    /**
     * @param string $level Nível a atribuir às mensagens recebidas
     */
    public function __construct(private readonly string $level = 'debug') {}

    /**
     * Regista uma mensagem usando o nível configurado.
     *
     * @param string $message Mensagem recebida pela API legada
     */
    public function write(string $message): void
    {
        TLog::log($this->level, $message);
    }
}
