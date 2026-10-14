<?php
declare(strict_types=1);

namespace App\Lib;

use Aurea\Auth\THash;
use Aurea\Database\TTransaction;

/**
 * Inicializa o schema e dados de demonstração do CRM em SQLite.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class CrmDemoDatabase
{
    public static function ensure(): void
    {
        $db = TTransaction::open('crm');
        try {
            $db->exec(
                'CREATE TABLE IF NOT EXISTS crm_cliente ('
                . 'id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, empresa TEXT, '
                . 'email TEXT, telefone TEXT, cidade TEXT, '
                . 'status TEXT NOT NULL DEFAULT "lead", created_at TEXT)'
            );
            $db->exec(
                'CREATE TABLE IF NOT EXISTS crm_oportunidade ('
                . 'id INTEGER PRIMARY KEY AUTOINCREMENT, cliente_id INTEGER NOT NULL, '
                . 'titulo TEXT NOT NULL, valor REAL NOT NULL DEFAULT 0, '
                . 'fase TEXT NOT NULL DEFAULT "Proposta", previsao TEXT, '
                . 'FOREIGN KEY(cliente_id) REFERENCES crm_cliente(id))'
            );
            $db->exec(
                'CREATE TABLE IF NOT EXISTS crm_tarefa ('
                . 'id INTEGER PRIMARY KEY AUTOINCREMENT, cliente_id INTEGER, '
                . 'titulo TEXT NOT NULL, vencimento TEXT, concluida INTEGER NOT NULL DEFAULT 0)'
            );
            $db->exec(
                'CREATE TABLE IF NOT EXISTS crm_user ('
                . 'id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, '
                . 'email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL)'
            );
            if ((int) $db->query('SELECT COUNT(*) FROM crm_user')->fetchColumn() === 0) {
                $insertUser = $db->prepare('INSERT INTO crm_user (nome, email, password_hash) VALUES (?, ?, ?)');
                $insertUser->execute(['Gestor CRM', 'demo@aurea.test', THash::make('demo123')]);
            }
            if ((int) $db->query('SELECT COUNT(*) FROM crm_cliente')->fetchColumn() === 0) {
                $db->exec(
                    "INSERT INTO crm_cliente (nome, empresa, email, telefone, cidade, status, created_at) VALUES "
                    . "('Ana Martins', 'Aurea Lda', 'ana@aurea.test', '912000001', 'Porto', 'cliente', datetime('now')), "
                    . "('Bruno Costa', 'Costa & Filhos', 'bruno@costa.test', '912000002', 'Lisboa', 'prospeccao', datetime('now')), "
                    . "('Carla Sousa', 'Norte Digital', 'carla@norte.test', '912000003', 'Braga', 'lead', datetime('now'))"
                );
                $db->exec(
                    "INSERT INTO crm_oportunidade (cliente_id, titulo, valor, fase, previsao) VALUES "
                    . "(1, 'Renovação anual', 12500, 'Negociação', '2026-11-15'), "
                    . "(2, 'Implementação CRM', 8400, 'Proposta', '2026-12-01'), "
                    . "(3, 'Diagnóstico comercial', 2200, 'Qualificação', '2026-11-05')"
                );
                $db->exec(
                    "INSERT INTO crm_tarefa (cliente_id, titulo, vencimento, concluida) VALUES "
                    . "(1, 'Ligar para Ana', '2026-10-20', 0), "
                    . "(2, 'Enviar proposta', '2026-10-22', 0), "
                    . "(3, 'Agendar demonstração', '2026-10-18', 1)"
                );
            }
            TTransaction::close();
        } catch (\Throwable $e) {
            TTransaction::rollback();
            throw $e;
        }
    }
}
