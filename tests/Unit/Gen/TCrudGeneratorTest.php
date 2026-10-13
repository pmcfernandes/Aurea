<?php
declare(strict_types=1);

namespace Tests\Unit\Gen;

use App\Model\Cliente;
use Aurea\Gen\TCrudGenerator;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Gen\TCrudGenerator */
final class TCrudGeneratorTest extends TestCase
{
    private function lint(string $code): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'crud') . '.php';
        file_put_contents($tmp, $code);
        try {
            exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $out, $code2);
            $this->assertSame(0, $code2, implode("\n", $out));
        } finally {
            @unlink($tmp);
        }
    }

    public function testGenerateFormContainsFragments(): void
    {
        $gen = new TCrudGenerator(Cliente::class);
        $code = $gen->generateForm('ClienteForm', [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'entry', 'required' => true],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'email'],
        ]);

        $this->assertStringContainsString('class ClienteForm', $code);
        $this->assertStringContainsString('TStandardFormTrait', $code);
        $this->assertStringContainsString('form_cliente', $code);
        $this->assertStringContainsString("new TEntry('nome')", $code);
        $this->assertStringContainsString("new TEmail('email')", $code);
        $this->assertStringContainsString('TRequiredValidator', $code);
        $this->assertStringContainsString('onSave', $code);
        $this->lint($code);
    }

    public function testGenerateListContainsFragments(): void
    {
        $gen = new TCrudGenerator(Cliente::class);
        $code = $gen->generateList('ClienteForm', 'ClienteList', [
            ['name' => 'nome', 'label' => 'Nome'],
            ['name' => 'cidade', 'label' => 'Cidade'],
        ]);

        $this->assertStringContainsString('class ClienteList', $code);
        $this->assertStringContainsString('TStandardListTrait', $code);
        $this->assertStringContainsString("new TColumn('nome', 'Nome')", $code);
        $this->assertStringContainsString("new TColumn('cidade', 'Cidade')", $code);
        $this->assertStringContainsString('onReload', $code);
        $this->assertStringContainsString('onDelete', $code);
        $this->lint($code);
    }

    /**
     * @dataProvider fieldTypeProvider
     */
    public function testFieldClassMapping(string $type, string $expectedClass): void
    {
        $gen = new TCrudGenerator(Cliente::class);
        $code = $gen->generateForm('XForm', [['name' => 'campo', 'label' => 'Campo', 'type' => $type]]);
        $this->assertStringContainsString("new {$expectedClass}('campo')", $code);
        $this->lint($code);
    }

    public static function fieldTypeProvider(): array
    {
        return [
            'entry' => ['entry', 'TEntry'],
            'email' => ['email', 'TEmail'],
            'number' => ['number', 'TNumber'],
            'date' => ['date', 'TDate'],
            'text' => ['text', 'TTextArea'],
            'unknown defaults to entry' => ['whatever', 'TEntry'],
        ];
    }

    public function testCustomDatabaseName(): void
    {
        $gen = new TCrudGenerator(Cliente::class, ' outro ');
        $code = $gen->generateForm('ClienteForm', []);
        $this->assertStringContainsString("setDatabase(' outro ')", $code);
        $this->lint($code);
    }
}
