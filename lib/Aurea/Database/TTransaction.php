<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TTransaction — gere transações PDO (uma por nome de config).
 *
 *   TTransaction::open('app');
 *   $obj->store();
 *   TTransaction::close();
 *   // ou TTransaction::rollback() em caso de erro
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTransaction
{
    /** @var array<string,PDO> */
    protected static array $conns = [];
    protected static ?string $active = null;
    /** @var string[] Contextos suspensos ao alternar entre conexões */
    protected static array $contexts = [];
    /** @var array<string,int> Profundidade da transação por conexão */
    protected static array $depths = [];
    protected static $logger = null;
    protected static bool $dump = false;
    protected static $loggerStrategy = null;
    protected static $loggerFunction = null;

    /**
     * Abre (ou reutiliza) a transação do nome de config.
     *
     * @param string $configName Nome do arquivo em config
     * @return PDO Conexão PDO ativa
     * @throws \Exception Se a config não for encontrada
     */
    public static function open(string $configName): PDO
    {
        if (!isset(self::$conns[$configName])) {
            self::$conns[$configName] = TConnection::openFile($configName);
        }
        return self::activate($configName);
    }

    /**
     * Abre transação a partir de um array de config (útil em testes e scripts).
     * Ex: TTransaction::openConfig(['type' => 'sqlite', 'name' => ':memory:']);
     */
    public static function openConfig(array $config, ?string $name = null): PDO
    {
        $name = $name ?? 'config_' . count(self::$conns);
        if (!isset(self::$conns[$name])) {
            self::$conns[$name] = TConnection::open($config);
        }
        return self::activate($name);
    }

    /**
     * Ativa uma conexão registada e inicia transação se preciso.
     * Reentradas na mesma conexão criam savepoints; outra conexão suspende o
     * contexto atual até ser fechada ou revertida.
     *
     * @param string $name Nome da conexão registada
     * @return PDO Conexão PDO ativa
     */
    protected static function activate(string $name): PDO
    {
        if (self::$active !== null && self::$active !== $name) {
            self::$contexts[] = self::$active;
        }
        self::$active = $name;
        $conn = self::$conns[$name];
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
            self::$depths[$name] = 1;
        } else {
            $depth = (self::$depths[$name] ?? 1) + 1;
            $conn->exec('SAVEPOINT aurea_tx_' . $depth);
            self::$depths[$name] = $depth;
        }
        return $conn;
    }

    /**
     * Obtém a conexão PDO da transação ativa.
     *
     * @return PDO Conexão PDO ativa
     * @throws \Exception Se nenhuma transação estiver aberta
     */
    public static function get(): PDO
    {
        if (!self::$active || !isset(self::$conns[self::$active])) {
            throw new \RuntimeException('Nenhuma transação aberta. Chame Transaction::open() primeiro.');
        }
        return self::$conns[self::$active];
    }

    /**
     * Obtém o nome da base de dados ativa.
     *
     * @return ?string Nome da config ativa ou null
     */
    public static function getDatabase(): ?string
    {
        return self::$active;
    }

    /**
     * Confirma a transação ativa ou liberta o savepoint da reentrada atual.
     * Ao fechar o nível externo, restaura o contexto anterior ou o limpa.
     */
    public static function close(): void
    {
        if (self::$active && isset(self::$conns[self::$active])) {
            $name = self::$active;
            $conn = self::$conns[self::$active];
            $depth = self::$depths[$name] ?? 1;
            if ($conn->inTransaction()) {
                if ($depth > 1) {
                    $conn->exec('RELEASE SAVEPOINT aurea_tx_' . $depth);
                    self::$depths[$name] = $depth - 1;
                    return;
                }
                $conn->commit();
            }
            unset(self::$depths[$name]);
            self::$active = array_pop(self::$contexts) ?: null;
        }
    }

    /**
     * Reverte a transação ativa ou o savepoint da reentrada atual.
     * Ao reverter o nível externo, restaura o contexto anterior ou o limpa.
     */
    public static function rollback(): void
    {
        if (self::$active && isset(self::$conns[self::$active])) {
            $name = self::$active;
            $conn = self::$conns[self::$active];
            $depth = self::$depths[$name] ?? 1;
            if ($conn->inTransaction()) {
                if ($depth > 1) {
                    $conn->exec('ROLLBACK TO SAVEPOINT aurea_tx_' . $depth);
                    $conn->exec('RELEASE SAVEPOINT aurea_tx_' . $depth);
                    self::$depths[$name] = $depth - 1;
                    return;
                }
                $conn->rollBack();
            }
            unset(self::$depths[$name]);
            self::$active = array_pop(self::$contexts) ?: null;
        }
    }

    /**
     * Confirma todas as transações abertas.
     */
    public static function closeAll(): void
    {
        foreach (self::$conns as $conn) {
            if ($conn->inTransaction()) {
                $conn->commit();
            }
        }
        self::$depths = [];
        self::$contexts = [];
        self::$active = null;
    }

    /**
     * Reverte todas as transações abertas.
     */
    public static function rollbackAll(): void
    {
        foreach (self::$conns as $conn) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
        }
        self::$depths = [];
        self::$contexts = [];
        self::$active = null;
    }

    /** Ativa log SQL no stdout/arquivo (debug) */

    public static function dump(?string $file = null): void
    {
        self::$dump = true;
        self::$logger = $file;
    }

    /** Define estratégia de log */

    public static function setLogger(\Aurea\Log\TLoggerInterface $logger): void
    {
        self::$loggerStrategy = $logger;
    }

    /** Define closure de log */

    public static function setLoggerFunction(callable $function): void
    {
        self::$loggerFunction = $function;
    }

    /**
     * Regista uma instrução SQL nos loggers configurados.
     *
     * @param string $sql Instrução SQL executada
     * @param array $params Parâmetros vinculados
     */
    public static function log(string $sql, array $params = []): void
    {
        $line = $sql . ($params ? ' ' . json_encode($params) : '');
        if (self::$loggerFunction) {
            (self::$loggerFunction)($line);
        }
        if (self::$loggerStrategy) {
            self::$loggerStrategy->write($line);
        }
        if (!self::$dump) {
            return;
        }
        $line .= PHP_EOL;
        if (self::$logger) {
            file_put_contents(self::$logger, $line, FILE_APPEND);
        } else {
            echo "<!-- SQL: " . htmlspecialchars($line) . " -->";
        }
    }

    /**
     * Executa callable dentro de transação (commit/rollback automáticos).
     *
     * @template T
     * @param callable():T $fn Trabalho a executar
     * @param string $configName Nome da config
     * @return T Retorno do callable
     */
    public static function transaction(callable $fn, string $configName = 'app'): mixed
    {
        self::open($configName);
        try {
            $result = $fn();
            self::close();
            return $result;
        } catch (\Throwable $e) {
            self::rollback();
            throw $e;
        }
    }
}
