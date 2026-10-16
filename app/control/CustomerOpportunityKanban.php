<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerOpportunity;
use Aurea\Core\TAction;
use Aurea\Core\TAssets;
use Aurea\Core\TPage;
use Aurea\Database\TTransaction;
use Aurea\Mvc\TView;
use Aurea\Widget\TKanban;

/** Pipeline visual de oportunidades, organizado por fase comercial. */
class CustomerOpportunityKanban extends TPage
{
    /** @var array<string,string> */
    private const STAGES = [
        'qualification' => 'Qualificação',
        'proposal' => 'Proposta',
        'negotiation' => 'Negociação',
        'won' => 'Ganha',
        'lost' => 'Perdida',
    ];

    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/opportunity-kanban.css');
    }

    public function onShow(array $param = []): void
    {
        $opportunitiesByStage = array_fill_keys(array_keys(self::STAGES), []);

        try {
            TTransaction::open('app');
            $opportunities = CustomerOpportunity::where('id', '>', 0)
                ->orderBy('expected_close_at', 'ASC')
                ->get();

            foreach ($opportunities as $opportunity) {
                if (!isset($opportunitiesByStage[$opportunity->stage])) {
                    continue;
                }

                $customer = $opportunity->customer();
                $opportunitiesByStage[$opportunity->stage][] = [
                    'id' => (string) $opportunity->id,
                    'title' => (string) $opportunity->title,
                    'customer' => $customer?->name ?? 'Cliente removido',
                    'amount' => (float) $opportunity->amount,
                    'expectedCloseAt' => (string) ($opportunity->expected_close_at ?? ''),
                ];
            }

            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }

        $kanban = new TKanban();
        $kanban->addClass('opportunity-kanban-board');
        foreach (self::STAGES as $stage => $label) {
            $cards = $opportunitiesByStage[$stage];
            $total = array_sum(array_column($cards, 'amount'));
            $kanban->addColumn(
                $stage,
                sprintf('%s · %d · %s €', $label, count($cards), number_format($total, 2, ',', '.'))
            );

            foreach ($cards as $card) {
                $details = sprintf(
                    "%s\n%s €%s",
                    $card['customer'],
                    number_format($card['amount'], 2, ',', '.'),
                    $card['expectedCloseAt'] === '' ? '' : ' · ' . $card['expectedCloseAt']
                );
                $kanban->addCard($stage, $card['id'], $card['title'], $details);
            }
        }
        $kanban->setMoveAction(new TAction([$this, 'onMoveCard']));

        $this->addHtml((new TView('opportunities/kanban'))
            ->set('kanban', $kanban->getContents())
            ->fetch());
    }

    /** Atualiza a fase após o movimento de um cartão no componente Kanban. */
    public function onMoveCard(array $param = []): void
    {
        $id = isset($param['key']) && ctype_digit((string) $param['key']) ? (int) $param['key'] : 0;
        $stage = (string) ($param['status'] ?? '');
        if ($id < 1 || !isset(self::STAGES[$stage])) {
            return;
        }

        try {
            TTransaction::open('app');
            $opportunity = new CustomerOpportunity($id);
            $opportunity->stage = $stage;
            $opportunity->store();
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }
    }
}
