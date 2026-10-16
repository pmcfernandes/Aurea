<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Customer;
use App\Model\CustomerActivity;
use App\Model\CustomerOpportunity;
use App\Model\CustomerDocument;
use Aurea\Base\TStandardForm;
use Aurea\Container\TTabs;
use Aurea\Core\TAction;
use Aurea\Core\TComponent;
use Aurea\Core\TScript;
use Aurea\Database\TTransaction;
use Aurea\Dialog\TWindow;
use Aurea\Form\TCombo;
use Aurea\Form\TEmail;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\THidden;
use Aurea\Form\TLabel;
use Aurea\Form\TField;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use Aurea\Validator\TEmailValidator;
use Aurea\Validator\TRequiredValidator;

/** Formulário de criação e edição de clientes. */
class CustomerForm extends TStandardForm
{
    private TComponent $activitiesTab;
    private TComponent $opportunitiesTab;
    private TComponent $documentsTab;

    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return Customer::class;
    }

    public function onShow(array $param = []): void
    {
        parent::onShow($param);
    }

    public function onEdit(array $param = []): void
    {
        parent::onEdit($param);
        $customerId = isset($param['key']) && ctype_digit((string) $param['key']) ? (int) $param['key'] : null;
        $this->loadActivities($customerId);
        $this->loadOpportunities($customerId);
        $this->loadDocuments($customerId);
    }

    public function onSave(array $param = []): void
    {
        parent::onSave($param);
        $id = $this->form->getForm()->getField('id')?->getValue();
        $this->loadActivities(is_numeric($id) && (int) $id > 0 ? (int) $id : null);
        $this->loadOpportunities(is_numeric($id) && (int) $id > 0 ? (int) $id : null);
        $this->loadDocuments(is_numeric($id) && (int) $id > 0 ? (int) $id : null);
    }

    /** Abre o formulário de actividade num modal associado ao cliente atual. */
    public function onNewActivity(array $param = []): void
    {
        $customerId = isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])
            ? (int) $param['customer_id']
            : 0;
        if ($customerId < 1) {
            return;
        }

        $this->onEdit(['key' => $customerId]);
        $activityForm = new CustomerActivityForm();
        $activityForm->onShow(['customer_id' => $customerId]);
        $this->showCustomerModal($activityForm, 'Nova actividade', $customerId);
    }

    /** Abre a edição de uma actividade num modal. */
    public function onEditActivity(array $param = []): void
    {
        $this->openRelatedModal($param, CustomerActivityForm::class, 'Editar actividade');
    }

    /** Abre o formulário de oportunidade num modal associado ao cliente atual. */
    public function onNewOpportunity(array $param = []): void
    {
        $customerId = isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])
            ? (int) $param['customer_id']
            : 0;
        if ($customerId < 1) {
            return;
        }

        $this->onEdit(['key' => $customerId]);
        $opportunityForm = new CustomerOpportunityForm();
        $opportunityForm->onShow(['customer_id' => $customerId]);
        $this->showCustomerModal($opportunityForm, 'Nova oportunidade', $customerId);
    }

    /** Abre a edição de uma oportunidade num modal. */
    public function onEditOpportunity(array $param = []): void
    {
        $this->openRelatedModal($param, CustomerOpportunityForm::class, 'Editar oportunidade');
    }

    /** Abre o formulário de documento num modal associado ao cliente atual. */
    public function onNewDocument(array $param = []): void
    {
        $customerId = isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])
            ? (int) $param['customer_id']
            : 0;
        if ($customerId < 1) {
            return;
        }
        $this->onEdit(['key' => $customerId]);
        $documentForm = new CustomerDocumentForm();
        $documentForm->onShow(['customer_id' => $customerId]);
        $this->showCustomerModal($documentForm, 'Novo documento', $customerId);
    }

    /** Abre a edição de um documento num modal. */
    public function onEditDocument(array $param = []): void
    {
        $this->openRelatedModal($param, CustomerDocumentForm::class, 'Editar documento');
    }

    protected function buildForm(): TFormBuilder
    {
        $form = (new TFormBuilder('customer'))
            ->setFormTitle('Dados do cliente')
            ->setClientValidation();
        $id = new THidden('id');
        $name = (new TEntry('name'))->setPlaceholder('Nome do cliente');
        $company = (new TEntry('company'))->setPlaceholder('Empresa');
        $email = (new TEmail('email'))->setPlaceholder('email@empresa.com');
        $phone = (new TEntry('phone'))->setPlaceholder('+351 000 000 000');
        $city = (new TEntry('city'))->setPlaceholder('Cidade');
        $status = (new TCombo('status'))->addItems([
            'lead' => 'Lead',
            'prospect' => 'Prospect',
            'customer' => 'Cliente',
            'inactive' => 'Inativo',
        ]);

        $name->addValidation('Nome', new TRequiredValidator());
        $email->addValidation('E-mail', new TEmailValidator());

        $form->getForm()->addField($id);
        $form->getForm()->add($id);
        foreach ([$name, $company, $email, $phone, $city, $status] as $field) {
            $form->getForm()->addField($field);
        }

        $general = new TComponent('div');
        $general->add($this->fieldRow(['Nome' => $name, 'Empresa' => $company]));
        $general->add($this->fieldRow(['E-mail' => $email, 'Telefone' => $phone]));
        $general->add($this->fieldRow(['Cidade' => $city, 'Estado' => $status]));

        $tabs = new TTabs();
        $tabs->appendTab('Geral', $general);
        $this->activitiesTab = new TComponent('div');
        $this->activitiesTab->add($this->emptyTab(
            'fa-list-check',
            'Guarde o cliente primeiro',
            'Depois de guardar poderá registar e consultar as suas actividades.'
        ));
        $tabs->appendTab('Actividades', $this->activitiesTab);
        $this->opportunitiesTab = new TComponent('div');
        $this->opportunitiesTab->add($this->emptyTab(
            'fa-bullseye',
            'Guarde o cliente primeiro',
            'Depois de guardar poderá registar e consultar oportunidades.'
        ));
        $tabs->appendTab('Oportunidades', $this->opportunitiesTab);
        $this->documentsTab = new TComponent('div');
        $this->documentsTab->add($this->emptyTab(
            'fa-folder-open',
            'Guarde o cliente primeiro',
            'Depois de guardar poderá registar e consultar documentos.'
        ));
        $tabs->appendTab('Documentos', $this->documentsTab);
        $form->addContent($tabs);
        $form->addAction('Guardar', new TAction([$this, 'onSave']), 'fa-save');
        $form->addActionLink('Voltar à lista', new TAction(['CustomerList', 'onShow']), 'fa-arrow-left');

        return $form;
    }

    /** @param array<string,TField> $fields */
    private function fieldRow(array $fields): TComponent
    {
        $row = new TComponent('div');
        $row->addClass('row g-3 mb-3');
        foreach ($fields as $label => $field) {
            $column = new TComponent('div');
            $column->addClass('col-12 col-md-6');
            $fieldLabel = new TLabel($label);
            $fieldLabel->setProperty('for', (string) $field->getProperty('id'));
            $column->add($fieldLabel);
            $column->add($field);
            $row->add($column);
        }
        return $row;
    }

    private function emptyTab(string $icon, string $title, string $description): TComponent
    {
        $content = new TComponent('div');
        $content->addClass('customer-tab-empty text-center py-5');
        $symbol = new TComponent('i');
        $symbol->addClass("fa-solid {$icon} d-block mb-3");
        $symbol->setProperty('aria-hidden', 'true');
        $heading = new TComponent('h2');
        $heading->addClass('h6 mb-2');
        $heading->addText($title);
        $text = new TComponent('p');
        $text->addClass('text-muted small mb-0');
        $text->addText($description);
        $content->add($symbol);
        $content->add($heading);
        $content->add($text);
        return $content;
    }

    private function loadActivities(?int $customerId): void
    {
        $this->activitiesTab->setChildren([]);
        if ($customerId === null || $customerId < 1) {
            $this->activitiesTab->add($this->emptyTab(
                'fa-list-check',
                'Guarde o cliente primeiro',
                'Depois de guardar poderá registar e consultar as suas actividades.'
            ));
            return;
        }

        $header = new TComponent('div');
        $header->addClass('d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3');
        $title = new TComponent('h2');
        $title->addClass('h6 mb-0');
        $title->addText('Actividades do cliente');
        $create = new TComponent('a');
        $create->addClass('btn btn-primary btn-sm');
        $create->setProperty('href', (new TAction([$this, 'onNewActivity'], ['customer_id' => $customerId]))->toHref());
        $create->addHtml('<i class="fa-solid fa-plus" aria-hidden="true"></i> Nova actividade');
        $header->add($title);
        $header->add($create);
        $this->activitiesTab->add($header);

        try {
            TTransaction::open('app');
            $activities = CustomerActivity::where('customer_id', '=', $customerId)
                ->orderBy('scheduled_at', 'DESC')
                ->get();
            TTransaction::close();
            $grid = new TDataGrid();
            foreach ([['type', 'Tipo'], ['subject', 'Assunto'], ['scheduled_at', 'Data'], ['status', 'Estado']] as [$field, $label]) {
                $grid->addColumn(new TColumn($field, $label));
            }
            $grid->addAction('Editar', new TAction([$this, 'onEditActivity'], ['customer_id' => $customerId]), 'fa-pen');
            $grid->setItems($activities);
            $this->activitiesTab->add($grid);
        } catch (\Throwable) {
            TTransaction::rollback();
            $this->activitiesTab->add($this->emptyTab(
                'fa-triangle-exclamation',
                'Não foi possível carregar actividades',
                'Tente atualizar a página.'
            ));
        }
    }

    private function loadOpportunities(?int $customerId): void
    {
        $this->opportunitiesTab->setChildren([]);
        if ($customerId === null || $customerId < 1) {
            $this->opportunitiesTab->add($this->emptyTab(
                'fa-bullseye',
                'Guarde o cliente primeiro',
                'Depois de guardar poderá registar e consultar oportunidades.'
            ));
            return;
        }

        $header = new TComponent('div');
        $header->addClass('d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3');
        $title = new TComponent('h2');
        $title->addClass('h6 mb-0');
        $title->addText('Oportunidades do cliente');
        $create = new TComponent('a');
        $create->addClass('btn btn-primary btn-sm');
        $create->setProperty('href', (new TAction([$this, 'onNewOpportunity'], ['customer_id' => $customerId]))->toHref());
        $create->addHtml('<i class="fa-solid fa-plus" aria-hidden="true"></i> Nova oportunidade');
        $header->add($title);
        $header->add($create);
        $this->opportunitiesTab->add($header);

        try {
            TTransaction::open('app');
            $opportunities = CustomerOpportunity::where('customer_id', '=', $customerId)
                ->orderBy('expected_close_at', 'ASC')
                ->get();
            TTransaction::close();
            $grid = new TDataGrid();
            foreach ([
                ['title', 'Oportunidade'],
                ['amount', 'Valor'],
                ['stage', 'Fase'],
                ['expected_close_at', 'Fecho previsto'],
                ['status', 'Estado'],
            ] as [$field, $label]) {
                $grid->addColumn(new TColumn($field, $label));
            }
            $grid->addAction('Editar', new TAction([$this, 'onEditOpportunity'], ['customer_id' => $customerId]), 'fa-pen');
            $grid->setItems($opportunities);
            $this->opportunitiesTab->add($grid);
        } catch (\Throwable) {
            TTransaction::rollback();
            $this->opportunitiesTab->add($this->emptyTab(
                'fa-triangle-exclamation',
                'Não foi possível carregar oportunidades',
                'Tente atualizar a página.'
            ));
        }
    }

    private function loadDocuments(?int $customerId): void
    {
        $this->documentsTab->setChildren([]);
        if ($customerId === null || $customerId < 1) {
            $this->documentsTab->add($this->emptyTab(
                'fa-folder-open',
                'Guarde o cliente primeiro',
                'Depois de guardar poderá registar e consultar documentos.'
            ));
            return;
        }
        $header = new TComponent('div');
        $header->addClass('d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3');
        $title = new TComponent('h2');
        $title->addClass('h6 mb-0');
        $title->addText('Documentos do cliente');
        $create = new TComponent('a');
        $create->addClass('btn btn-primary btn-sm');
        $create->setProperty('href', (new TAction([$this, 'onNewDocument'], ['customer_id' => $customerId]))->toHref());
        $create->addHtml('<i class="fa-solid fa-plus" aria-hidden="true"></i> Novo documento');
        $header->add($title);
        $header->add($create);
        $this->documentsTab->add($header);
        try {
            TTransaction::open('app');
            $documents = CustomerDocument::where('customer_id', '=', $customerId)
                ->orderBy('created_at', 'DESC')
                ->get();
            TTransaction::close();
            $grid = new TDataGrid();
            foreach ([
                ['title', 'Documento'],
                ['document_type', 'Tipo'],
                ['file_name', 'Ficheiro'],
                ['expires_at', 'Validade'],
                ['status', 'Estado'],
            ] as [$field, $label]) {
                $grid->addColumn(new TColumn($field, $label));
            }
            $grid->addAction('Editar', new TAction([$this, 'onEditDocument'], ['customer_id' => $customerId]), 'fa-pen');
            $grid->setItems($documents);
            $this->documentsTab->add($grid);
        } catch (\Throwable) {
            TTransaction::rollback();
            $this->documentsTab->add($this->emptyTab(
                'fa-triangle-exclamation',
                'Não foi possível carregar documentos',
                'Tente atualizar a página.'
            ));
        }
    }

    /** @param class-string<TStandardForm> $formClass */
    private function openRelatedModal(array $param, string $formClass, string $title): void
    {
        $customerId = isset($param['customer_id']) && ctype_digit((string) $param['customer_id'])
            ? (int) $param['customer_id']
            : 0;
        $key = isset($param['key']) && ctype_digit((string) $param['key'])
            ? (int) $param['key']
            : 0;
        if ($customerId < 1 || $key < 1) {
            return;
        }
        $this->onEdit(['key' => $customerId]);
        $relatedForm = new $formClass();
        $relatedForm->onShow(['key' => $key]);
        $this->showCustomerModal($relatedForm, $title, $customerId);
    }

    /** Mostra um formulário relacionado e atualiza as tabs do cliente quando o modal é fechado. */
    private function showCustomerModal(object $form, string $title, int $customerId): void
    {
        $window = new TWindow($title, 760);
        $window->addClass('customer-related-modal');
        $window->setContent($form);
        $window->addButton('Fechar');
        $modalId = $window->getModalId();
        $redirect = json_encode(
            (new TAction(['CustomerForm', 'onEdit'], ['key' => $customerId]))->toHref(),
            JSON_THROW_ON_ERROR
        );
        TScript::create(<<<JS
            var modal = document.getElementById('{$modalId}');
            if (modal) {
                var save = modal.querySelector('.modal-body .card-footer button.btn-primary[type="submit"]');
                var footer = modal.querySelector('.modal-footer');
                if (save && footer) {
                    var form = save.closest('form');
                    if (form && form.id) {
                        save.setAttribute('form', form.id);
                    }
                    save.closest('.card-footer')?.classList.add('d-none');
                    footer.prepend(save);
                }
                modal.addEventListener('hidden.bs.modal', function () {
                    window.location = {$redirect};
                }, { once: true });
            }
            JS);
        $window->show();
    }
}
