<?php
declare(strict_types=1);

namespace Aurea\Log;

/**
 * Logger em ficheiro XML.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TXmlLogger implements TLoggerInterface
{
    /**
     * Cria o logger de arquivo XML.
     *
     * @param string $file Caminho do arquivo de log
     */
    public function __construct(protected string $file) {}

    /**
     * Regista uma mensagem no arquivo XML como nova entrada.
     *
     * @param string $message Mensagem a registar
     */
    public function write(string $message): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $loaded = false;
        if (file_exists($this->file)) {
            $prev = libxml_use_internal_errors(true);
            $loaded = $doc->load($this->file);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
        if (!$loaded || !$doc->documentElement) {
            $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?><log/>');
        }
        $entry = $doc->createElement('entry');
        $entry->setAttribute('time', date('Y-m-d H:i:s'));
        $node = $doc->createElement('message');
        $node->appendChild($doc->createTextNode($message));
        $entry->appendChild($node);
        $doc->documentElement->appendChild($entry);
        $doc->save($this->file);
    }
}
