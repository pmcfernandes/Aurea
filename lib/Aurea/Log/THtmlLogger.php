<?php
declare(strict_types=1);

namespace Aurea\Log;

use Aurea\Database\TConnection;


/**
 * Logger em ficheiro HTML.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class THtmlLogger implements TLoggerInterface
{
    /**
     * Cria o logger de arquivo HTML.
     *
     * @param string $file Caminho do arquivo de log
     */
    public function __construct(protected string $file) {}

    /**
     * Regista uma mensagem no arquivo HTML.
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($this->file)) {
            file_put_contents($this->file, "<html><body><table border='1' cellpadding='4'>\n");
        }
        file_put_contents(
            $this->file,
            '<tr><td>' . date('Y-m-d H:i:s') . '</td><td>'
            . htmlspecialchars($message) . "</td></tr>\n",
            FILE_APPEND
        );
    }
}
