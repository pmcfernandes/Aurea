<?php
declare(strict_types=1);

namespace App\Control;

use App\Lib\CrmDemoDatabase;
use Aurea\Auth\TAuth;
use Aurea\Auth\THash;
use Aurea\Core\TAction;
use Aurea\Core\TPage;
use Aurea\Core\TScript;
use Aurea\Core\TSession;
use Aurea\Dialog\TMessage;
use Aurea\Log\TLog;
use Aurea\Form\TEmail;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use Aurea\Form\TPassword;
use Aurea\Validator\TEmailValidator;
use Aurea\Validator\TRequiredValidator;
use Aurea\Database\TTransaction;

/** Página de autenticação da demonstração CRM. */
class CrmLogin extends TPage
{
    private TFormBuilder $form;

    public function __construct()
    {
        parent::__construct();
        $this->form = (new TFormBuilder('crm_login'))->setFormTitle('Entrar no CRM');
        $email = (new TEmail('email'))->setPlaceholder('demo@aurea.test');
        $password = (new TPassword('password'))->setPlaceholder('Palavra-passe');
        $email->addValidation('E-mail', new TRequiredValidator);
        $email->addValidation('E-mail', new TEmailValidator);
        $password->addValidation('Palavra-passe', new TRequiredValidator);
        $this->form->addFields([new TLabel('E-mail')], [$email]);
        $this->form->addFields([new TLabel('Palavra-passe')], [$password]);
        $this->form->addAction('Entrar', new TAction([$this, 'onLogin']), 'fa-right-to-bracket');
        $this->addHtml('<div class="row justify-content-center"><div class="col-12 col-md-6 col-lg-4">');
        $this->add($this->form);
        $this->addHtml('<p class="text-muted small mt-3">Demo: <code>demo@aurea.test</code> / <code>demo123</code></p></div></div>');
    }

    /**
     * Mostra o formulário ou reencaminha utilizadores já autenticados.
     *
     * @param array<string,mixed> $param Parâmetros do pedido
     */
    public function onShow(array $param = []): void
    {
        if (TAuth::check()) {
            TScript::redirect(new TAction(['CrmDashboard', 'onShow']));
        }
    }

    /**
     * Autentica o utilizador, renova hashes BCrypt obsoletos e regista o evento.
     *
     * @param array<string,mixed> $param Parâmetros do pedido
     */
    public function onLogin(array $param = []): void
    {
        try {
            CrmDemoDatabase::ensure();
            $this->form->setData($_POST + $param);
            $this->form->validate();
            $data = $this->form->getData();
            TTransaction::open('crm');
            $stmt = TTransaction::get()->prepare('SELECT id, nome, email, password_hash FROM crm_user WHERE email = ?');
            $stmt->execute([strtolower(trim((string) $data->email))]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$user || !THash::verify((string) $data->password, (string) $user['password_hash'])) {
                TTransaction::close();
                TLog::warning('Falha de autenticação CRM');
                throw new \RuntimeException('E-mail ou palavra-passe inválidos.');
            }
            if (THash::needsRehash((string) $user['password_hash'])) {
                $update = TTransaction::get()->prepare('UPDATE crm_user SET password_hash = ? WHERE id = ?');
                $update->execute([THash::make((string) $data->password), $user['id']]);
                TLog::info('Hash de palavra-passe CRM renovado', ['user_id' => $user['id']]);
            }
            TTransaction::close();
            TAuth::login((string) $user['id'], (string) $user['nome'], ['email' => $user['email']]);
            TLog::info('Sessão CRM iniciada', ['user_id' => $user['id']]);
            $after = TSession::getValue('crm_after_login', ['class' => 'CrmDashboard', 'method' => 'onShow']);
            TSession::clear('crm_after_login');
            $target = is_array($after) ? $after : [];
            (new TMessage('success', 'Sessão iniciada.'))->show();
            TScript::redirect(new TAction([$target['class'] ?? 'CrmDashboard', $target['method'] ?? 'onShow']));
        } catch (\Throwable $error) {
            TTransaction::rollback();
            (new TMessage('error', $error->getMessage()))->show();
        }
    }

    /**
     * Encerra a sessão atual e regista o evento sem incluir dados pessoais.
     *
     * @param array<string,mixed> $param Parâmetros do pedido
     */
    public function onLogout(array $param = []): void
    {
        TLog::info('Sessão CRM terminada', ['user_id' => TAuth::id()]);
        TAuth::logout();
        TScript::redirect(new TAction(['CrmLogin', 'onShow']));
    }
}
