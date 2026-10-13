<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Core\TSession;

/**
 * TCaptchaField — campo captcha matemático sem dependências.
 *
 * Mostra uma soma `A + B = ?` com números de 1 a 9 gerados uma vez e
 * guardados em TSession até à validação.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCaptchaField extends TField
{
    /**
     * Cria o campo captcha com o nome informado.
     *
     * @param string $name Nome do campo
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setProperty('type', 'text');
        $this->setProperty('autocomplete', 'off');
    }

    /**
     * Obtém a chave de sessão usada para guardar o desafio.
     *
     * @return string Chave de sessão do desafio
     */
    protected function sessionKey(): string
    {
        return 'captcha_' . $this->name;
    }

    /**
     * Gera um novo desafio com dois números de 1 a 9.
     *
     * Garante que o novo desafio difere do anterior, se informado.
     *
     * @param array|null $previous Desafio anterior a evitar
     * @return array Desafio com as chaves a, b e answer
     */
    protected function generate(?array $previous = null): array
    {
        do {
            $a = random_int(1, 9);
            $b = random_int(1, 9);
        } while ($previous !== null && ($previous['a'] ?? null) === $a && ($previous['b'] ?? null) === $b);
        return ['a' => $a, 'b' => $b, 'answer' => $a + $b];
    }

    /**
     * Garante que existe um desafio guardado em sessão e devolve-o.
     *
     * @return array Desafio atual com as chaves a, b e answer
     */
    protected function ensureChallenge(): array
    {
        $data = TSession::getValue($this->sessionKey());
        if (!is_array($data) || !isset($data['a'], $data['b'])) {
            $data = $this->generate();
            TSession::setValue($this->sessionKey(), $data);
        }
        return $data;
    }

    /**
     * Roda o desafio, gerando e guardando um novo em sessão.
     */
    protected function rotate(): void
    {
        $old = TSession::getValue($this->sessionKey());
        TSession::setValue($this->sessionKey(), $this->generate(is_array($old) ? $old : null));
    }

    /**
     * Obtém o HTML do captcha (pergunta `A + B = ?` mais input).
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $challenge = $this->ensureChallenge();
        $question = $challenge['a'] . ' + ' . $challenge['b'] . ' = ?';
        $html = '';
        if ($this->label !== '') {
            $html .= '<label for="field_' . htmlspecialchars($this->name, ENT_QUOTES) . '">'
                . htmlspecialchars($this->label, ENT_QUOTES) . '</label>';
        }
        $html .= '<span class="captcha-question">' . htmlspecialchars($question, ENT_QUOTES) . '</span>';
        $html .= '<input type="text" name="' . htmlspecialchars($this->name, ENT_QUOTES) . '"'
            . ' id="field_' . htmlspecialchars($this->name, ENT_QUOTES) . '"'
            . ' class="form-control captcha-input" autocomplete="off">';
        return $html;
    }

    /**
     * Valida a resposta contra o desafio guardado e roda o desafio.
     *
     * Compara o valor submetido com a resposta guardada em sessão, gera
     * um novo desafio e lança exceção quando a resposta está errada.
     *
     * @throws \Exception Quando a resposta do captcha está errada
     */
    public function validate(): void
    {
        parent::validate();
        $stored = TSession::getValue($this->sessionKey());
        $expected = is_array($stored) ? ($stored['answer'] ?? null) : null;
        $this->rotate();
        $given = $this->value ?? $_POST[$this->name] ?? $_GET[$this->name] ?? null;
        if ($expected === null || $given === null || $given === '' || (int) $given !== (int) $expected) {
            throw new \Exception('Resposta do captcha incorreta.');
        }
    }

    /**
     * Ignora qualquer valor (o captcha não se preenche por código).
     *
     * @param mixed $value Valor ignorado
     * @return static
     */
    public function setValue(mixed $value): static
    {
        return $this;
    }
}
