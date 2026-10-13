<?php
declare(strict_types=1);

namespace Tests\Unit\Validator;

use Aurea\Validator\TEmailValidator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Validator\TEmailValidator */
final class TEmailValidatorTest extends TestCase
{
    public function testEmptyPasses(): void
    {
        $v = new TEmailValidator();
        $v->validate('Email', null);
        $v->validate('Email', '');
        $this->assertTrue(true);
    }

    public function testValidPasses(): void
    {
        $v = new TEmailValidator();
        $v->validate('Email', 'user@example.com');
        $v->validate('Email', 'nome.sobrenome@empresa.com.br');
        $this->assertTrue(true);
    }

    public function testInvalidThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TEmailValidator())->validate('Email', 'not-an-email');
    }

    public function testIncompleteThrows(): void
    {
        $this->expectException(\Exception::class);
        (new TEmailValidator())->validate('Email', 'user@');
    }
}
