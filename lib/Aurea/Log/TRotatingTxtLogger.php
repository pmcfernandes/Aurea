<?php
declare(strict_types=1);

namespace Aurea\Log;

/**
 * TRotatingTxtLogger — ficheiro de texto com rotação por tamanho.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TRotatingTxtLogger implements TLoggerInterface
{
    /**
     * Cria o logger com rotação por tamanho.
     *
     * @param string $file Caminho do arquivo de log
     * @param int $maxBytes Tamanho máximo antes de rodar
     * @param int $keep Número de arquivos antigos a manter
     */
    public function __construct(
        protected string $file,
        protected int $maxBytes = 1048576,
        protected int $keep = 5
    ) {}

    /**
     * Regista uma mensagem (roda o arquivo se exceder o tamanho).
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (is_file($this->file) && filesize($this->file) >= $this->maxBytes) {
            $this->rotate();
        }
        file_put_contents(
            $this->file,
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Roda os arquivos de log (.1, .2, ...).
     */
    protected function rotate(): void
    {
        for ($i = $this->keep - 1; $i >= 1; $i--) {
            $from = $this->file . '.' . $i;
            $to = $this->file . '.' . ($i + 1);
            if (is_file($from)) {
                rename($from, $to);
            }
        }
        rename($this->file, $this->file . '.1');
    }
}
