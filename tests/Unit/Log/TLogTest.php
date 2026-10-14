<?php
declare(strict_types=1);

namespace Tests\Unit\Log;

use Aurea\Log\TLog;
use Aurea\Log\TLogContext;
use Aurea\Log\TLoggerInterface;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Log\TLog */
final class TLogTest extends TestCase
{
    protected function tearDown(): void
    {
        TLog::setLogger(null);
        TLogContext::clear();
    }

    public function testWritesStructuredRecordWithContextAndRedaction(): void
    {
        $messages = [];
        $logger = new class($messages) implements TLoggerInterface {
            public function __construct(private array &$messages) {}
            public function write(string $message): void { $this->messages[] = $message; }
        };
        TLog::setLogger($logger);
        TLogContext::beginRequest('request-123');
        TLogContext::set('user_id', '7');

        TLog::info('Cliente atualizado', ['password' => 'segredo', 'cliente_id' => 12]);

        $record = json_decode($messages[0], true);
        $this->assertSame('info', $record['level']);
        $this->assertSame('request-123', $record['request_id']);
        $this->assertSame('7', $record['user_id']);
        $this->assertSame('[redacted]', $record['password']);
        $this->assertSame(12, $record['cliente_id']);
    }

    public function testWriterAdaptsLegacyLoggerConsumers(): void
    {
        $messages = [];
        TLog::setLogger(new class($messages) implements TLoggerInterface {
            public function __construct(private array &$messages) {}
            public function write(string $message): void { $this->messages[] = $message; }
        });

        TLog::writer('warning')->write('SQL lento');

        $this->assertSame('warning', json_decode($messages[0], true)['level']);
    }
}
