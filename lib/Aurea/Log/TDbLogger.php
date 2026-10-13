<?php
declare(strict_types=1);

namespace Aurea\Log;

use Aurea\Database\TConnection;


/**
 * Grava o log numa tabela (colunas: id, quando, mensagem). Usa conexão própria.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDbLogger implements TLoggerInterface
{
    /**
     * Cria o logger de base de dados.
     *
     * @param string $configName Nome da config em config
     * @param string $table Tabela de log
     */
    public function __construct(protected string $configName, protected string $table = 'log') {}

    /**
     * Regista uma mensagem na tabela de log.
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void
    {
        $conn = TConnection::openFile($this->configName);
        $conn->exec("CREATE TABLE IF NOT EXISTS {$this->table} (id INTEGER PRIMARY KEY AUTOINCREMENT, quando VARCHAR(19), mensagem TEXT)");
        $stmt = $conn->prepare("INSERT INTO {$this->table} (quando, mensagem) VALUES (?, ?)");
        $stmt->execute([date('Y-m-d H:i:s'), $message]);
    }
}
