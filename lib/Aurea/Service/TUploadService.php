<?php
declare(strict_types=1);

namespace Aurea\Service;

use Aurea\Form\TUploader;
use Aurea\Http\THttpRequest;
use Aurea\Http\THttpResponse;

/**
 * Endpoint para uploads assíncronos autenticados por token.
 *
 * Registe uma instância num TRestServer e encaminhe para onUpload(). O token
 * é validado antes de qualquer acesso a $_FILES.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TUploadService
{
    /** @var callable|null fn(string $token, THttpRequest $request): bool|string */
    private $tokenValidator;

    /**
     * @param string $targetDirectory Diretório privado de destino
     * @param string[]|null $allowedExtensions Extensões permitidas
     * @param int $maxSize Limite de tamanho em bytes
     * @param string[]|null $allowedMimeTypes MIME types reais permitidos
     * @param callable|null $tokenValidator Recebe token e pedido; devolve true ou motivo da rejeição
     */
    public function __construct(
        private string $targetDirectory,
        private ?array $allowedExtensions = null,
        private int $maxSize = 10485760,
        private ?array $allowedMimeTypes = null,
        ?callable $tokenValidator = null
    ) {
        $this->tokenValidator = $tokenValidator;
    }

    /**
     * Processa o ficheiro indicado em `field` (por omissão `file`).
     *
     * @param array<string,mixed> $param Token e nome opcional do campo
     * @param THttpRequest|null $request Pedido a validar; null captura o pedido atual
     * @return array{path:string}
     * @throws \RuntimeException Para token, campo ou upload inválidos
     */
    public function onUpload(array $param = [], ?THttpRequest $request = null): array
    {
        $request ??= THttpRequest::capture();
        $token = (string) ($param['token'] ?? $request->get('token', ''));
        if ($this->tokenValidator === null) {
            throw new \RuntimeException('Upload requer validador de token.', 500);
        }
        $valid = ($this->tokenValidator)($token, $request);
        if ($valid !== true) {
            throw new \RuntimeException(is_string($valid) && $valid !== '' ? $valid : 'Não autorizado.', 401);
        }
        $field = (string) ($param['field'] ?? 'file');
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field)) {
            throw new \RuntimeException('Campo de upload inválido.', 400);
        }
        $path = TUploader::save($field, $this->targetDirectory, $this->allowedExtensions, $this->maxSize, $this->allowedMimeTypes);
        if ($path === null) {
            throw new \RuntimeException('Nenhum ficheiro enviado.', 400);
        }
        return ['path' => $path];
    }

    /**
     * Variante que devolve uma resposta HTTP estruturada para uso direto.
     * @param THttpRequest $request Pedido contendo token e ficheiro
     * @return THttpResponse Resposta 201 ou envelope de erro
     */
    public function handle(THttpRequest $request): THttpResponse
    {
        try {
            return THttpResponse::json(['data' => $this->onUpload([], $request)], 201);
        } catch (\Throwable $e) {
            $status = (int) $e->getCode();
            $status = $status >= 400 && $status < 600 ? $status : 500;
            return THttpResponse::json(['error' => $e->getMessage(), 'code' => $status], $status);
        }
    }
}
