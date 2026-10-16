<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\CrmUser;
use Aurea\Auth\TAuth;
use Aurea\Core\TAction;
use Aurea\Core\TAssets;
use Aurea\Core\TCsrf;
use Aurea\Core\TComponent;
use Aurea\Core\TPage;
use Aurea\Core\TScript;
use Aurea\Core\TSession;
use Aurea\Database\TTransaction;
use Aurea\Form\TButton;
use Aurea\Form\TEmail;
use Aurea\Form\TForm;
use Aurea\Form\TLabel;
use Aurea\Form\TPassword;
use Aurea\Log\TLog;
use Aurea\Mvc\TView;
use Aurea\Validator\TEmailValidator;
use Aurea\Validator\TRequiredValidator;

/** Página de autenticação do CRM. */
class CrmLogin extends TPage
{
    private TForm $form;

    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/login.css');
        TAssets::addJs('/assets/login.js');

        $this->form = $this->buildForm();
        $this->addHtml($this->renderForm());
    }

    /** Mostra o formulário ou encaminha uma sessão já autenticada. */
    public function onShow(array $param = []): void
    {
        if (TAuth::check()) {
            TScript::redirect(new TAction(['CrmDashboard', 'onShow']));
        }
    }

    /** Valida as credenciais submetidas e inicia a sessão. */
    public function onLogin(array $param = []): void
    {
        $email = strtolower(trim((string) ($_POST['email'] ?? $param['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? $param['password'] ?? '');

        if (!TCsrf::validate($_POST[TCsrf::FIELD] ?? $param[TCsrf::FIELD] ?? null)) {
            $this->setError('A sessão expirou. Atualize a página e tente novamente.');
            return;
        }

        $this->form->setData(['email' => $email, 'password' => $password]);
        try {
            $this->form->validate();
        } catch (\Throwable $error) {
            $this->setError($error->getMessage());
            return;
        }

        try {
            TTransaction::open('crm');
            $user = CrmUser::findByEmail($email);
            if ($user === null || !$user->verifiesPassword($password)) {
                TTransaction::close();
                TLog::warning('Falha de autenticação CRM');
                $this->setError('E-mail ou palavra-passe incorretos.');
                return;
            }
            $user->refreshPasswordHash($password);
            TTransaction::close();

            TAuth::login((string) $user->id, (string) $user->nome, ['email' => $user->email]);
            TCsrf::rotate();
            TLog::info('Sessão CRM iniciada', ['user_id' => $user->id]);
            $afterLogin = TSession::getValue('crm_after_login', ['class' => 'CrmDashboard', 'method' => 'onShow']);
            TSession::clear('crm_after_login');
            $target = is_array($afterLogin) ? $afterLogin : [];
            TScript::redirect(new TAction([
                $target['class'] ?? 'CrmDashboard',
                $target['method'] ?? 'onShow',
            ]));
        } catch (\Throwable $error) {
            TTransaction::rollback();
            TLog::error('Não foi possível autenticar utilizador CRM', ['exception' => $error]);
            $this->setError('Não foi possível iniciar sessão. Tente novamente dentro de momentos.');
        }
    }

    /** Termina a sessão atual. */
    public function onLogout(array $param = []): void
    {
        TLog::info('Sessão CRM terminada', ['user_id' => TAuth::id()]);
        TAuth::logout();
        TScript::redirect(new TAction(['CrmLogin', 'onShow']));
    }

    private function setError(string $message): void
    {
        $this->setChildren([]);
        $this->addHtml($this->renderForm($message));
    }

    private function renderForm(?string $error = null): string
    {
        return (new TView('crm/login'))
            ->set('form', $this->form->getContents())
            ->set('error', $error)
            ->fetch();
    }

    /** Monta o formulário com componentes reutilizáveis do framework. */
    private function buildForm(): TForm
    {
        $form = new TForm('crm_login');
        $form->setProperty('action', (new TAction([$this, 'onLogin']))->toHref());
        $form->setProperty('novalidate', 'novalidate');
        $form->addClass('login-form');
        $form->enableCsrf();

        $email = (new TEmail('email'))
            ->setId('email')
            ->setPlaceholder('nome@empresa.com')
            ->setProperty('autocomplete', 'email')
            ->setProperty('inputmode', 'email')
            ->setProperty('autofocus', 'autofocus')
            ->setProperty('required', 'required');
        $email->addValidation('E-mail', new TRequiredValidator());
        $email->addValidation('E-mail', new TEmailValidator());

        $password = (new TPassword('password'))
            ->setId('password')
            ->setPlaceholder('Introduza a sua palavra-passe')
            ->setProperty('autocomplete', 'current-password')
            ->setProperty('required', 'required');
        $password->addValidation('Palavra-passe', new TRequiredValidator());

        $form->addField($email);
        $form->addField($password);
        $form->add($this->fieldGroup('E-mail', $email, 'fa-regular fa-envelope'));
        $form->add($this->passwordGroup($password));
        $form->add($this->rememberField());

        $submit = new TButton('Entrar na minha conta <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>');
        $submit->addClass('login-submit');
        $form->add($submit);

        return $form;
    }

    private function fieldGroup(string $label, TComponent $field, string $icon): TComponent
    {
        $group = new TComponent('div');
        $group->addClass('login-field');
        $fieldLabel = new TLabel($label);
        $fieldLabel->setProperty('for', (string) $field->getProperty('id'));
        $group->add($fieldLabel);

        $input = new TComponent('div');
        $input->addClass('login-input');
        $input->addHtml('<i class="' . $icon . '" aria-hidden="true"></i>');
        $input->add($field);
        $group->add($input);
        return $group;
    }

    private function passwordGroup(TPassword $password): TComponent
    {
        $group = new TComponent('div');
        $group->addClass('login-field');
        $labelRow = new TComponent('div');
        $labelRow->addClass('login-label-row');
        $label = new TLabel('Palavra-passe');
        $label->setProperty('for', 'password');
        $labelRow->add($label);
        $labelRow->addHtml('<a href="#recuperar">Esqueceu-se da palavra-passe?</a>');
        $group->add($labelRow);

        $input = new TComponent('div');
        $input->addClass('login-input');
        $input->addHtml('<i class="fa-solid fa-lock" aria-hidden="true"></i>');
        $input->add($password);
        $toggle = new TButton('<i class="fa-regular fa-eye" aria-hidden="true"></i>');
        $toggle->setProperty('type', 'button');
        $toggle->setProperty('aria-label', 'Mostrar palavra-passe');
        $toggle->setProperty('aria-pressed', 'false');
        $toggle->addClass('login-password-toggle');
        $input->add($toggle);
        $group->add($input);
        return $group;
    }

    private function rememberField(): TComponent
    {
        $label = new TComponent('label');
        $label->addClass('login-remember');
        $checkbox = new TComponent('input');
        $checkbox->setProperty('type', 'checkbox');
        $checkbox->setProperty('name', 'remember');
        $checkbox->setProperty('value', '1');
        $label->add($checkbox);
        $label->addText('Manter sessão iniciada');
        return $label;
    }
}
