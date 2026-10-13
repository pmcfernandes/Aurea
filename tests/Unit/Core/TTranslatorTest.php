<?php
declare(strict_types=1);

namespace Tests\Unit\Core;

use Aurea\Core\TTranslator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Core\TTranslator */
final class TTranslatorTest extends TestCase
{
    protected function tearDown(): void
    {
        TTranslator::setLanguage('pt');
    }

    public function testPtEn(): void
    {
        TTranslator::setLanguage('pt');
        $this->assertSame('pt', TTranslator::getLanguage());
        $this->assertSame('Registro salvo', TTranslator::translate('Record saved'));
        $this->assertSame('Registro salvo', _t('Record saved'));
        TTranslator::setLanguage('en');
        $this->assertSame('Record saved', _t('Record saved'));
    }

    public function testSprintfAndFallback(): void
    {
        TTranslator::setLanguage('en');
        $this->assertSame('The field Nome is required.', _t('The field %s is required.', 'Nome'));
        $this->assertSame('Mensagem X', _t('Mensagem X'));
    }

    public function testAddDictionary(): void
    {
        TTranslator::addDictionary('pt', ['Hi X' => 'Olá X']);
        $this->assertSame('Olá X', _t('Hi X'));
    }
}
