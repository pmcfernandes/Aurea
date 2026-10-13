<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Form\TFieldList;
use Aurea\Form\TEntry;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TFieldList */
final class TFieldListTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->sessionBackup = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_SESSION = $this->sessionBackup;
    }

    public function testAddFieldAddRowGetContents(): void
    {
        $fl = new TFieldList('itens');
        $this->assertSame($fl, $fl->addField((new TEntry('qtd'))->setLabel('Qtd')));
        $this->assertSame($fl, $fl->addRow(['qtd' => '3']));
        $html = $fl->getContents();
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<th>Qtd</th>', $html);
        $this->assertStringContainsString('name="itens[qtd][]"', $html);
        $this->assertStringContainsString('value="3"', $html);
    }

    public function testSetRows(): void
    {
        $fl = new TFieldList('itens');
        $fl->addField(new TEntry('nome'));
        $this->assertSame($fl, $fl->setRows([['nome' => 'a'], ['nome' => 'b']]));
        $html = $fl->getContents();
        $this->assertStringContainsString('value="a"', $html);
        $this->assertStringContainsString('value="b"', $html);
    }

    public function testGetRowsFromPost(): void
    {
        $fl = new TFieldList('itens');
        $_POST['itens'] = ['qtd' => ['1', '2'], 'nome' => ['a', 'b']];
        $this->assertSame(
            [['qtd' => '1', 'nome' => 'a'], ['qtd' => '2', 'nome' => 'b']],
            $fl->getRows()
        );
    }

    public function testGetRowsEmptyWhenMissing(): void
    {
        $fl = new TFieldList('itens');
        unset($_POST['itens']);
        $this->assertSame([], $fl->getRows());
        $_POST['itens'] = 'nao-array';
        $this->assertSame([], $fl->getRows());
    }

    public function testGetContentsWithoutRowsRendersOneEmptyRow(): void
    {
        $fl = new TFieldList('itens');
        $fl->addField(new TEntry('qtd'));
        $html = $fl->getContents();
        $this->assertStringContainsString('<tbody', $html);
        $this->assertStringContainsString('+ Adicionar', $html);
    }
}
