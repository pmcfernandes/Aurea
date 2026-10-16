<?php
declare(strict_types=1);

namespace Aurea\Mail;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * TMail — envio de mensagens através de PHPMailer.
 *
 * @example
 * $mail = TMail::fromConfig(require APP_ROOT . '/config/mail.php');
 * $mail->addTo('destinatario@example.test', 'Destinatário')
 *     ->setSubject('Assunto')
 *     ->setHtmlBody('<p>Mensagem</p>')
 *     ->send();
 */
final class TMail
{
    private PHPMailer $mailer;

    /** @param array<string,mixed> $config Configuração SMTP e remetente. */
    public function __construct(array $config = [])
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mailer->isHTML(true);
        $this->configure($config);
    }

    /** @param array<string,mixed> $config Configuração SMTP e remetente. */
    public static function fromConfig(array $config): self
    {
        return new self($config);
    }

    /** @param array<string,mixed> $config Configuração SMTP e remetente. */
    public function configure(array $config): self
    {
        $smtp = is_array($config['smtp'] ?? null) ? $config['smtp'] : [];
        $host = trim((string) ($smtp['host'] ?? ''));

        if ($host !== '') {
            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->Port = max(1, (int) ($smtp['port'] ?? 587));
            $this->mailer->SMTPAuth = (bool) ($smtp['auth'] ?? ($smtp['username'] ?? '') !== '');
            $this->mailer->Username = (string) ($smtp['username'] ?? '');
            $this->mailer->Password = (string) ($smtp['password'] ?? '');
            $this->mailer->SMTPSecure = $this->encryption((string) ($smtp['encryption'] ?? 'tls'));
            $this->mailer->SMTPAutoTLS = (bool) ($smtp['auto_tls'] ?? true);
            $this->mailer->Timeout = max(1, (int) ($smtp['timeout'] ?? 15));
        }

        $from = is_array($config['from'] ?? null) ? $config['from'] : [];
        $address = trim((string) ($from['address'] ?? ''));
        if ($address !== '') {
            $this->setFrom($address, (string) ($from['name'] ?? ''));
        }

        return $this;
    }

    public function setFrom(string $address, string $name = ''): self
    {
        $this->mailer->setFrom($address, $name);

        return $this;
    }

    public function addTo(string $address, string $name = ''): self
    {
        $this->mailer->addAddress($address, $name);

        return $this;
    }

    public function addCc(string $address, string $name = ''): self
    {
        $this->mailer->addCC($address, $name);

        return $this;
    }

    public function addBcc(string $address, string $name = ''): self
    {
        $this->mailer->addBCC($address, $name);

        return $this;
    }

    public function addReplyTo(string $address, string $name = ''): self
    {
        $this->mailer->addReplyTo($address, $name);

        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->mailer->Subject = $subject;

        return $this;
    }

    public function setHtmlBody(string $html): self
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $html;

        return $this;
    }

    public function setTextBody(string $text): self
    {
        $this->mailer->AltBody = $text;

        return $this;
    }

    public function addAttachment(string $path, string $name = ''): self
    {
        $this->mailer->addAttachment($path, $name);

        return $this;
    }

    /**
     * Envia a mensagem configurada.
     *
     * @throws \RuntimeException Quando PHPMailer não conseguir enviar a mensagem
     */
    public function send(): bool
    {
        try {
            return $this->mailer->send();
        } catch (PHPMailerException $exception) {
            throw new \RuntimeException('Não foi possível enviar o e-mail: ' . $exception->getMessage(), 0, $exception);
        }
    }

    private function encryption(string $encryption): string
    {
        return match (strtolower(trim($encryption))) {
            '', 'none' => '',
            'ssl', 'smtps' => PHPMailer::ENCRYPTION_SMTPS,
            'tls', 'starttls' => PHPMailer::ENCRYPTION_STARTTLS,
            default => throw new \InvalidArgumentException('Encriptação SMTP inválida. Use tls, ssl ou none.'),
        };
    }
}
