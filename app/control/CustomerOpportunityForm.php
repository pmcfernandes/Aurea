<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerOpportunity;
use Aurea\Base\TStandardForm;
use Aurea\Core\TAction;
use Aurea\Form\TCombo;
use Aurea\Form\TDate;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\THidden;
use Aurea\Form\TLabel;
use Aurea\Validator\TRequiredValidator;

/** Formulário de oportunidades comerciais. */
class CustomerOpportunityForm extends TStandardForm
{
    public function __construct()
    {
        parent::__construct();
        $this->setBeforeStore(static function (CustomerOpportunity $opportunity): void {
            $opportunity->amount = self::normaliseAmount((string) $opportunity->amount);
            $opportunity->expected_close_at = trim((string) $opportunity->expected_close_at) ?: null;
        });
    }

    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return CustomerOpportunity::class;
    }

    public function onShow(array $param = []): void
    {
        parent::onShow($param);
        if (!isset($param['key']) && isset($param['customer_id'])) {
            $this->form->setData(['customer_id' => (int) $param['customer_id']]);
        }
    }

    protected function buildForm(): TFormBuilder
    {
        $form = (new TFormBuilder('customer_opportunity'))
            ->setFormTitle('Oportunidade')
            ->setClientValidation();
        $id = new THidden('id');
        $customerId = new THidden('customer_id');
        $title = (new TEntry('title'))->setPlaceholder('Nome da oportunidade');
        $amount = (new TEntry('amount'))->setPlaceholder('0,00')->setProperty('inputmode', 'decimal');
        $stage = (new TCombo('stage'))->addItems([
            'qualification' => 'Qualificação',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'won' => 'Ganha',
            'lost' => 'Perdida',
        ]);
        $expectedClose = new TDate('expected_close_at');
        $status = (new TCombo('status'))->addItems([
            'open' => 'Aberta',
            'won' => 'Ganha',
            'lost' => 'Perdida',
        ]);
        $title->addValidation('Título', new TRequiredValidator());
        foreach ([$id, $customerId] as $field) {
            $form->getForm()->addField($field);
            $form->getForm()->add($field);
        }
        $form->addFields([new TLabel('Título'), new TLabel('Valor')], [$title, $amount]);
        $form->addFields([new TLabel('Fase'), new TLabel('Fecho previsto')], [$stage, $expectedClose]);
        $form->addFields([new TLabel('Estado')], [$status]);
        $form->addAction('Guardar', new TAction([$this, 'onSave']), 'fa-save');
        $form->addActionLink('Voltar às oportunidades', new TAction(['CustomerOpportunityList', 'onShow']), 'fa-arrow-left');
        return $form;
    }

    private static function normaliseAmount(string $amount): string
    {
        $amount = trim(str_replace([' ', "\u{00A0}"], '', $amount));
        if ($amount === '') {
            return '0.00';
        }
        if (str_contains($amount, ',')) {
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
        }
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('O valor da oportunidade é inválido.');
        }
        return number_format((float) $amount, 2, '.', '');
    }
}
