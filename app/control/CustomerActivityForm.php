<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerActivity;
use Aurea\Base\TStandardForm;
use Aurea\Core\TAction;
use Aurea\Form\TCombo;
use Aurea\Form\TDateTimeField;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\THidden;
use Aurea\Form\TLabel;
use Aurea\Form\TTextArea;
use Aurea\Validator\TRequiredValidator;

/** Formulário de actividades de um cliente. */
class CustomerActivityForm extends TStandardForm
{
    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return CustomerActivity::class;
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
        $form = (new TFormBuilder('customer_activity'))
            ->setFormTitle('Actividade')
            ->setClientValidation();
        $id = new THidden('id');
        $customerId = new THidden('customer_id');
        $type = (new TCombo('type'))->addItems(['call' => 'Chamada', 'meeting' => 'Reunião', 'email' => 'E-mail', 'task' => 'Tarefa']);
        $subject = (new TEntry('subject'))->setPlaceholder('Assunto da actividade');
        $scheduledAt = new TDateTimeField('scheduled_at');
        $status = (new TCombo('status'))->addItems(['planned' => 'Planeada', 'completed' => 'Concluída', 'cancelled' => 'Cancelada']);
        $description = (new TTextArea('description'))->setPlaceholder('Notas ou detalhes da actividade');

        $subject->addValidation('Assunto', new TRequiredValidator());
        foreach ([$id, $customerId] as $field) {
            $form->getForm()->addField($field);
            $form->getForm()->add($field);
        }
        $form->addFields([new TLabel('Tipo'), new TLabel('Estado')], [$type, $status]);
        $form->addFields([new TLabel('Assunto'), new TLabel('Data e hora')], [$subject, $scheduledAt]);
        $form->addFields([new TLabel('Descrição')], [$description]);
        $form->addAction('Guardar', new TAction([$this, 'onSave']), 'fa-save');
        $form->addActionLink('Voltar às actividades', new TAction(['CustomerActivityList', 'onShow']), 'fa-arrow-left');
        return $form;
    }
}
