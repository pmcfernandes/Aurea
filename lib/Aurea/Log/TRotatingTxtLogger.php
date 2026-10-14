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
    ) {
        if ($this->maxBytes < 1 || $this->keep < 1) {
            throw new \InvalidArgumentException('maxBytes e keep devem ser positivos.');
        }
    }

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
        $lock = fopen($this->file . '.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            error_log('Aurea: não foi possível bloquear o ficheiro de log.');
            return;
        }
        try {
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
            if (is_file($this->file) && (int) filesize($this->file) + strlen($line) > $this->maxBytes) {
                $this->rotate();
            }
            if (file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX) === false) {
                throw new \RuntimeException('Não foi possível escrever o ficheiro de log.');
            }
        } catch (\Throwable $error) {
            error_log('Aurea: falha ao escrever o log: ' . $error->getMessage());
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
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
                if (is_file($to)) {
                    unlink($to);
                }
                if (!rename($from, $to)) {
                    throw new \RuntimeException('Não foi possível rodar o ficheiro de log.');
                }
            }
        }
        if (!rename($this->file, $this->file . '.1')) {
            throw new \RuntimeException('Não foi possível rodar o ficheiro de log.');
        }
    }
}
