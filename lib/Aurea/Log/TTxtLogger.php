<?php
declare(strict_types=1);

namespace Aurea\Log;

use Aurea\Database\TConnection;


/**
 * Logger em ficheiro de texto.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTxtLogger implements TLoggerInterface
{
    /**
     * Cria o logger de arquivo texto.
     *
     * @param string $file Caminho do arquivo de log
     */
    public function __construct(protected string $file) {}

    /**
     * Regista uma mensagem no arquivo texto.
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(
            $this->file,
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}
