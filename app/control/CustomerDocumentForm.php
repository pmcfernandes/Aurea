<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CustomerDocument;
use Aurea\Base\TStandardForm;
use Aurea\Core\TAction;
use Aurea\Form\TCombo;
use Aurea\Form\TDate;
use Aurea\Form\TEntry;
use Aurea\Form\TFileField;
use Aurea\Form\TFormBuilder;
use Aurea\Form\THidden;
use Aurea\Form\TLabel;
use Aurea\Form\TUploader;
use Aurea\Validator\TRequiredValidator;

/** Formulário de documentos de cliente. */
class CustomerDocumentForm extends TStandardForm
{
    protected function getDatabase(): string
    {
        return 'app';
    }

    protected function getActiveRecord(): string
    {
        return CustomerDocument::class;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setBeforeStore(static function (CustomerDocument $document): void {
            $document->expires_at = trim((string) $document->expires_at) ?: null;
            $path = TUploader::save(
                'attachment',
                APP_ROOT . '/var/uploads/customer-documents',
                ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'],
                10 * 1024 * 1024
            );
            if ($path !== null) {
                $document->file_path = str_replace('\\', '/', str_replace(APP_ROOT . DIRECTORY_SEPARATOR, '', $path));
                $document->file_name = basename((string) ($_FILES['attachment']['name'] ?? $path));
            }
        });
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
        $form = (new TFormBuilder('customer_document'))
            ->setFormTitle('Documento')
            ->setClientValidation();
        $id = new THidden('id');
        $customerId = new THidden('customer_id');
        $fileName = new THidden('file_name');
        $filePath = new THidden('file_path');
        $title = (new TEntry('title'))->setPlaceholder('Título do documento');
        $type = (new TCombo('document_type'))->addItems([
            'contract' => 'Contrato',
            'proposal' => 'Proposta',
            'invoice' => 'Fatura',
            'other' => 'Outro',
        ]);
        $expiresAt = new TDate('expires_at');
        $status = (new TCombo('status'))->addItems([
            'valid' => 'Válido',
            'expired' => 'Expirado',
            'archived' => 'Arquivado',
        ]);
        $attachment = (new TFileField('attachment'))
            ->setAllowedExtensions(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'])
            ->setMaxSize(10 * 1024 * 1024);
        $title->addValidation('Título', new TRequiredValidator());
        foreach ([$id, $customerId, $fileName, $filePath] as $field) {
            $form->getForm()->addField($field);
            $form->getForm()->add($field);
        }
        $form->addFields([new TLabel('Título'), new TLabel('Tipo')], [$title, $type]);
        $form->addFields([new TLabel('Validade'), new TLabel('Estado')], [$expiresAt, $status]);
        $form->addFields([new TLabel('Ficheiro (máx. 10 MB)')], [$attachment]);
        $form->addAction('Guardar', new TAction([$this, 'onSave']), 'fa-save');
        $form->addActionLink('Voltar aos documentos', new TAction(['CustomerDocumentList', 'onShow']), 'fa-arrow-left');
        return $form;
    }
}
