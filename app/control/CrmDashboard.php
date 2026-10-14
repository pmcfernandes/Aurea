<?php
declare(strict_types=1);

namespace App\Control;

use App\Lib\CrmDemoDatabase;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TPage;
use Aurea\Database\TTransaction;
use Aurea\Widget\TNumericIndicator;

/**
 * Dashboard operacional da demonstração CRM.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class CrmDashboard extends TPage
{
    public function onShow(array $param = []): void
    {
        CrmDemoDatabase::ensure();
        TTransaction::open('crm');
        $db = TTransaction::get();
        $clients = (int) $db->query('SELECT COUNT(*) FROM crm_cliente')->fetchColumn();
        $pipeline = (float) $db->query('SELECT COALESCE(SUM(valor), 0) FROM crm_oportunidade')->fetchColumn();
        $pending = (int) $db->query('SELECT COUNT(*) FROM crm_tarefa WHERE concluida = 0')->fetchColumn();
        $opps = $db->query('SELECT o.titulo, o.valor, o.fase, c.nome AS cliente FROM crm_oportunidade o JOIN crm_cliente c ON c.id = o.cliente_id ORDER BY o.id DESC')->fetchAll(\PDO::FETCH_ASSOC);
        TTransaction::close();
        $this->addHtml('<h1 class="h3 mb-4">CRM Demo</h1><div class="row g-3"></div>');
        $cards = new TComponent('div');
        $cards->addClass('row g-3 mb-4');
        foreach ([['Clientes', $clients, null], ['Pipeline', $pipeline, 'EUR'], ['Tarefas pendentes', $pending, null]] as [$label, $value, $currency]) {
            $column = new TComponent('div');
            $column->addClass('col-md-4');
            $card = new TComponent('div');
            $card->addClass('card card-body');
            $card->addHtml('<div class="text-muted small">' . htmlspecialchars($label) . '</div>');
            $metric = (new TNumericIndicator($value))->setFormat($currency ? 2 : 0)->setCurrency($currency);
            $card->add($metric);
            $column->add($card);
            $cards->add($column);
        }
        $this->add($cards);
        $html='<div class="card"><div class="card-header fw-bold">Oportunidades recentes</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Cliente</th><th>Oportunidade</th><th>Fase</th><th class="text-end">Valor</th></tr></thead><tbody>';
        foreach ($opps as $opp) {
            $html .= '<tr><td>' . htmlspecialchars($opp['cliente']) . '</td><td>' . htmlspecialchars($opp['titulo']) . '</td><td><span class="badge text-bg-primary">' . htmlspecialchars($opp['fase']) . '</span></td><td class="text-end">' . number_format((float) $opp['valor'], 2, ',', '.') . ' €</td></tr>';
        }
        $html .= '</tbody></table></div></div><a class="btn btn-primary mt-3" href="' . (new TAction(['CrmClienteList', 'onShow']))->toHref() . '"><i class="fa-solid fa-users"></i> Gerir clientes</a>';
        $this->addHtml($html);
    }
}
