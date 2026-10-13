<?php
declare(strict_types=1);

namespace Aurea\Database;

use PDO;

/**
 * TConnection — abre conexões PDO a partir de config arrays.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TConnection
{
    /**
     * Abre uma conexão PDO a partir de um array de configuração.
     *
     * @param array $config Configuração tipo/host/name/user/pass
     * @return PDO Conexão PDO configurada
     */
    public static function open(array $config): PDO
    {
        $type = strtolower($config['type'] ?? 'sqlite');
        $name = $config['name'] ?? ':memory:';
        $host = $config['host'] ?? '';
        $port = $config['port'] ?? '';
        $user = $config['user'] ?? null;
        $pass = $config['pass'] ?? null;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        switch ($type) {
            case 'sqlite':
                return new PDO("sqlite:{$name}", null, null, $options);
            case 'mysql':
                $port = $port ?: '3306';
                $char = $config['char'] ?? 'utf8mb4';
                return new PDO("mysql:host={$host};port={$port};dbname={$name};charset={$char}", $user, $pass, $options);
            case 'pgsql':
                $port = $port ?: '5432';
                return new PDO("pgsql:host={$host};port={$port};dbname={$name}", $user, $pass, $options);
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                $port = $port ? ",{$port}" : '';
                return new PDO("sqlsrv:Server={$host}{$port};Database={$name}", $user, $pass, $options);
            case 'oracle':
                $tns = $config['tns'] ?? "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$host})(PORT={$port}))(CONNECT_DATA=(SERVICE_NAME={$name})))";
                return new PDO("oci:dbname={$tns};charset=" . ($config['char'] ?? 'AL32UTF8'), $user, $pass, $options);
            case 'fbird':
                return new PDO("firebird:dbname={$host}:{$name}", $user, $pass, $options);
            default:
                throw new \RuntimeException("Driver de banco desconhecido: {$type}");
        }
    }

    /** Lê config em config/<nome>.php que devolve array */

    public static function openFile(string $configName): PDO
    {
        $base = defined('APP_ROOT') ? APP_ROOT . '/config' : 'config';
        $file = "{$base}/{$configName}.php";
        if (!file_exists($file)) {
            throw new \RuntimeException("Config de banco não encontrada: {$file}");
        }
        $config = require $file;
        return self::open($config);
    }
}
