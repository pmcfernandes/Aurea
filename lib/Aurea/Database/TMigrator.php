<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TMigrator — gere migrations versionadas da base de dados.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TMigrator
{
    protected PDO $conn;
    /** @var array<string, array{type: string, payload: mixed}> */
    protected array $migrations = [];

    /**
     * Cria o migrador para a conexão informada.
     *
     * @param PDO $conn Conexão PDO onde aplicar as migrations
     */
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Adiciona uma migration em SQL (pode ter várias instruções separadas por `;`).
     *
     * @param string $version Versão da migration (ex.: "001")
     * @param string $sql Instruções SQL a executar
     * @return static
     */
    public function add(string $version, string $sql): static
    {
        $this->migrations[$version] = ['type' => 'sql', 'payload' => $sql];
        return $this;
    }

    /**
     * Adiciona uma migration por callback que recebe a conexão PDO.
     *
     * @param string $version Versão da migration (ex.: "002")
     * @param callable $fn Função a executar com a conexão (fn(PDO $conn))
     * @return static
     */
    public function addCallback(string $version, callable $fn): static
    {
        $this->migrations[$version] = ['type' => 'callback', 'payload' => $fn];
        return $this;
    }

    /**
     * Aplica as migrations pendentes por ordem de versão.
     *
     * Cria a tabela `migration(version, applied_at)` se necessário. Cada
     * migration corre dentro de uma transação; como DDL pode não ser
     * transacional em alguns drivers, executa na mesma e regista a versão.
     *
     * @return array Lista das versões aplicadas nesta corrida
     */
    public function migrate(): array
    {
        $this->ensureTable();
        $done = [];
        foreach ($this->pending() as $version) {
            $def = $this->migrations[$version];
            $this->conn->beginTransaction();
            try {
                if ($def['type'] === 'callback') {
                    ($def['payload'])($this->conn);
                } else {
                    foreach ($this->splitSql((string) $def['payload']) as $stmt) {
                        $this->conn->exec($stmt);
                    }
                }
                $insert = $this->conn->prepare('INSERT INTO migration (version, applied_at) VALUES (?, ?)');
                $insert->execute([$version, date('Y-m-d H:i:s')]);
                $this->conn->commit();
            } catch (\Throwable $e) {
                if ($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                throw $e;
            }
            $done[] = $version;
        }
        return $done;
    }

    /**
     * Obtém as versões já aplicadas na base de dados.
     *
     * @return array Lista de versões aplicadas, por ordem
     */
    public function applied(): array
    {
        $this->ensureTable();
        $rows = $this->conn->query('SELECT version FROM migration ORDER BY version')->fetchAll(PDO::FETCH_COLUMN);
        return array_values($rows ?: []);
    }

    /**
     * Obtém as versões registadas ainda por aplicar.
     *
     * @return array Lista de versões pendentes, por ordem
     */
    public function pending(): array
    {
        $done = $this->applied();
        $keys = array_keys($this->migrations);
        sort($keys, SORT_STRING);
        return array_values(array_diff($keys, $done));
    }

    /**
     * Cria a tabela de controlo das migrations se ainda não existir.
     */
    protected function ensureTable(): void
    {
        $this->conn->exec('CREATE TABLE IF NOT EXISTS migration (version VARCHAR(255) PRIMARY KEY, applied_at VARCHAR(30))');
    }

    /**
     * Divide um bloco SQL em instruções individuais pelo `;`.
     *
     * @param string $sql Bloco SQL com uma ou várias instruções
     * @return array Lista de instruções não vazias
     */
    protected function splitSql(string $sql): array
    {
        $stmts = [];
        foreach (explode(';', $sql) as $part) {
            $stmt = trim($part);
            if ($stmt !== '') {
                $stmts[] = $stmt;
            }
        }
        return $stmts;
    }
}
