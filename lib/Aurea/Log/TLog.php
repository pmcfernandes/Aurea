<?php
declare(strict_types=1);

namespace Aurea\Log;

/**
 * Fachada para registos estruturados com nível e contexto do pedido.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TLog
{
    private static ?TLoggerInterface $logger = null;
    private const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    /**
     * Define o destino que receberá linhas JSON estruturadas.
     *
     * @param TLoggerInterface|null $logger Destino ou null para desativar o registo
     */
    public static function setLogger(?TLoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Regista uma mensagem estruturada. Contextos sensíveis são redigidos.
     *
     * @param string $level Nível RFC 5424: debug, info, notice, warning, error,
     *                      critical, alert ou emergency
     * @param string $message Mensagem do evento
     * @param array<string,mixed> $context Contexto adicional do evento
     * @throws \InvalidArgumentException Se o nível não for suportado
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (self::$logger === null) {
            return;
        }
        $level = strtolower($level);
        if (!in_array($level, self::LEVELS, true)) {
            throw new \InvalidArgumentException("Nível de log inválido: {$level}");
        }
        $record = array_merge(TLogContext::all(), self::normalize($context), [
            'timestamp' => gmdate('c'),
            'level' => $level,
            'message' => $message,
        ]);
        self::$logger->write((string) json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
    }

    /**
     * Regista um evento de diagnóstico.
     *
     * @param string $message Mensagem do evento
     * @param array<string,mixed> $context Contexto adicional
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    /**
     * Regista um evento informativo.
     *
     * @param string $message Mensagem do evento
     * @param array<string,mixed> $context Contexto adicional
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Regista um aviso que não interrompe a execução.
     *
     * @param string $message Mensagem do evento
     * @param array<string,mixed> $context Contexto adicional
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /**
     * Regista uma falha da aplicação.
     *
     * @param string $message Mensagem do evento
     * @param array<string,mixed> $context Contexto adicional
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /**
     * Cria um adaptador para subsistemas que aceitam apenas TLoggerInterface.
     *
     * @param string $level Nível aplicado às mensagens escritas pelo adaptador
     * @return TLoggerInterface Adaptador configurado
     */
    public static function writer(string $level = 'debug'): TLoggerInterface
    {
        return new TLogWriter($level);
    }

    /** @param array<string,mixed> $context @return array<string,mixed> */
    private static function normalize(array $context): array
    {
        $normalized = [];
        foreach ($context as $key => $value) {
            $name = (string) $key;
            if (preg_match('/pass(word)?|secret|token|authorization|cookie/i', $name)) {
                $normalized[$name] = '[redacted]';
            } elseif ($value instanceof \Throwable) {
                $normalized[$name] = ['type' => $value::class, 'message' => $value->getMessage(), 'code' => $value->getCode()];
            } elseif (is_array($value)) {
                $normalized[$name] = self::normalize($value);
            } elseif (is_scalar($value) || $value === null) {
                $normalized[$name] = $value;
            } else {
                $normalized[$name] = ['type' => get_debug_type($value)];
            }
        }
        return $normalized;
    }
}
