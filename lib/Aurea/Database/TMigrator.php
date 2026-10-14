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
    /** @var array<int,bool> Locks por conexão no processo atual */
    protected static array $locks = [];
    protected ?string $lockToken = null;
    protected string $lockName = 'aurea_migrator';
    protected int $lockTimeout = 300;
    protected PDO $conn;
    /** @var array<string, array{type: string, payload: mixed}> */
    protected array $migrations = [];
    /** @var array<string, array{type: string, payload: mixed}> */
    protected array $rollbacks = [];

    /**
     * Cria o migrador para a conexão informada.
     *
     * @param PDO $conn Conexão PDO onde aplicar as migrations
     */
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /** Configura o nome lógico e a expiração do lock persistente de migrations. */
    public function setLock(string $name, int $timeout = 300): static
    {
        if ($name === '' || $timeout < 1) {
            throw new \InvalidArgumentException('O lock de migration requer nome e timeout positivos.');
        }
        $this->lockName = $name;
        $this->lockTimeout = $timeout;
        return $this;
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
     * Regista rollback de uma versão (SQL ou callback).
     */
    public function addRollback(string $version, string|callable $down): static
    {
        $this->rollbacks[$version] = is_string($down)
            ? ['type' => 'sql', 'payload' => $down]
            : ['type' => 'callback', 'payload' => $down];
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
        $this->acquireLock();
        try {
            $this->ensureTable();
            $this->assertAppliedChecksums();
            $done = [];
            foreach ($this->pending() as $version) {
            $def = $this->migrations[$version];
            $ownTransaction = !$this->conn->inTransaction();
            if ($ownTransaction) {
                $this->conn->beginTransaction();
            }
            try {
                if ($def['type'] === 'callback') {
                    ($def['payload'])($this->conn);
                } else {
                    foreach ($this->splitSql((string) $def['payload']) as $stmt) {
                        $this->conn->exec($stmt);
                    }
                }
                $insert = $this->conn->prepare('INSERT INTO migration (version, applied_at, checksum) VALUES (?, ?, ?)');
                $insert->execute([$version, date('Y-m-d H:i:s'), $this->checksum($version)]);
                if ($ownTransaction) {
                    $this->conn->commit();
                }
            } catch (\Throwable $e) {
                if ($ownTransaction && $this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                throw $e;
            }
                $done[] = $version;
            }
            return $done;
        } finally {
            $this->releaseLock();
        }
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
     * Reverte a última versão aplicada (ou versão indicada) usando rollback registado.
     *
     * @throws \RuntimeException Se não houver rollback para a versão
     */
    public function rollback(?string $version = null): string
    {
        $this->acquireLock();
        try {
        $this->ensureTable();
        $applied = $this->applied();
        if (!$applied) {
            throw new \RuntimeException('Nada a reverter.');
        }
        $target = $version ?? end($applied);
        if (!in_array($target, $applied, true)) {
            throw new \RuntimeException("Versão {$target} não aplicada.");
        }
        $down = $this->rollbacks[$target] ?? null;
        if (!$down) {
            throw new \RuntimeException("Sem rollback registado para {$target}.");
        }
        $ownTransaction = !$this->conn->inTransaction();
        if ($ownTransaction) {
            $this->conn->beginTransaction();
        }
        try {
            if ($down['type'] === 'callback') {
                ($down['payload'])($this->conn);
            } else {
                foreach ($this->splitSql((string) $down['payload']) as $stmt) {
                    $this->conn->exec($stmt);
                }
            }
            $del = $this->conn->prepare('DELETE FROM migration WHERE version = ?');
            $del->execute([$target]);
            if ($ownTransaction) {
                $this->conn->commit();
            }
        } catch (\Throwable $e) {
            if ($ownTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
        return $target;
        } finally {
            $this->releaseLock();
        }
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
        $this->conn->exec('CREATE TABLE IF NOT EXISTS migration (version VARCHAR(255) PRIMARY KEY, applied_at VARCHAR(30), checksum VARCHAR(64))');
        $this->conn->exec('CREATE TABLE IF NOT EXISTS migration_lock (name VARCHAR(255) PRIMARY KEY, token VARCHAR(64) NOT NULL, locked_at INTEGER NOT NULL)');
        try {
            $this->conn->exec('ALTER TABLE migration ADD COLUMN checksum VARCHAR(64)');
        } catch (\Throwable) {
            // A coluna já existe na maioria dos drivers; a compatibilidade com
            // instalações antigas é verificada apenas quando há checksum salvo.
        }
    }

    /** Deteta alterações acidentais em migrations já aplicadas. */
    protected function assertAppliedChecksums(): void
    {
        $rows = $this->conn->query('SELECT version, checksum FROM migration')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $row) {
            $version = (string) $row['version'];
            if (($row['checksum'] ?? '') !== '' && isset($this->migrations[$version]) && !hash_equals((string) $row['checksum'], $this->checksum($version))) {
                throw new \RuntimeException("Checksum da migration {$version} não corresponde ao conteúdo registado.");
            }
        }
    }

    protected function checksum(string $version): string
    {
        $migration = $this->migrations[$version];
        return hash('sha256', $migration['type'] === 'sql' ? (string) $migration['payload'] : 'callback:' . $version);
    }

    protected function acquireLock(): void
    {
        $key = spl_object_id($this->conn);
        if (isset(self::$locks[$key])) { throw new \RuntimeException('Já existe uma execução de migrations nesta conexão.'); }
        $this->conn->exec('CREATE TABLE IF NOT EXISTS migration_lock (name VARCHAR(255) PRIMARY KEY, token VARCHAR(64) NOT NULL, locked_at INTEGER NOT NULL)');
        $now = time();
        $token = bin2hex(random_bytes(24));
        try {
            $insert = $this->conn->prepare('INSERT INTO migration_lock (name, token, locked_at) VALUES (?, ?, ?)');
            $insert->execute([$this->lockName, $token, $now]);
        } catch (\Throwable $e) {
            $expire = $this->conn->prepare('DELETE FROM migration_lock WHERE name = ? AND locked_at < ?');
            $expire->execute([$this->lockName, $now - $this->lockTimeout]);
            try {
                $insert = $this->conn->prepare('INSERT INTO migration_lock (name, token, locked_at) VALUES (?, ?, ?)');
                $insert->execute([$this->lockName, $token, $now]);
            } catch (\Throwable) {
                throw new \RuntimeException('As migrations já estão em execução por outro processo.', 0, $e);
            }
        }
        self::$locks[$key] = true;
        $this->lockToken = $token;
    }

    /** Liberta somente o lock persistente pertencente a esta instância. */
    protected function releaseLock(): void
    {
        $key = spl_object_id($this->conn);
        if ($this->lockToken !== null) {
            try {
                $delete = $this->conn->prepare('DELETE FROM migration_lock WHERE name = ? AND token = ?');
                $delete->execute([$this->lockName, $this->lockToken]);
            } finally {
                $this->lockToken = null;
            }
        }
        unset(self::$locks[$key]);
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
