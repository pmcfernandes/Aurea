<?php
declare(strict_types=1);

namespace Aurea\Log;

use Aurea\Database\TConnection;


/**
 * Loggers.
 * Ligue ao TTransaction para auditar o SQL:
 *
 *   TTransaction::setLogger(new TTxtLogger('app/log/sql.log'));
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
interface TLoggerInterface
{
    /**
     * Regista uma mensagem de log.
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void;
}
