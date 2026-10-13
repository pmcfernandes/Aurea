<?php
declare(strict_types=1);

namespace Tests\Unit\Template;

use Aurea\Template\TTemplateParser;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Template\TTemplateParser */
final class TTemplateParserTest extends TestCase
{
    public function testParseVars(): void
    {
        $p = new TTemplateParser();
        $this->assertSame('Olá Ana!', $p->parse('Olá {nome}!', ['nome' => 'Ana']));
    }

    public function testParseEscapesByDefault(): void
    {
        $p = new TTemplateParser();
        $this->assertSame('&lt;b&gt;', $p->parse('{nome}', ['nome' => '<b>']));
    }

    public function testParseRaw(): void
    {
        $p = new TTemplateParser();
        $this->assertSame('<b>x</b>', $p->parse('{html!}', ['html' => '<b>x</b>']));
    }

    public function testParseObjectAttr(): void
    {
        $p = new TTemplateParser();
        $obj = new \stdClass();
        $obj->nome = 'Ana';
        $this->assertSame('Ana', $p->parse('{u->nome}', ['u' => $obj]));
        $this->assertSame('Bia', $p->parse('{u->nome}', ['u' => ['nome' => 'Bia']]));
    }

    public function testParseLoops(): void
    {
        $p = new TTemplateParser();
        $tpl = '<!--[loop items]-->{items->nome};<!--[/loop items]-->';
        $out = $p->parse($tpl, ['items' => [['nome' => 'Ana'], ['nome' => 'Bia']]]);
        $this->assertSame('Ana;Bia;', $out);
    }

    public function testParseLoopsObjects(): void
    {
        $p = new TTemplateParser();
        $a = new \stdClass();
        $a->nome = 'Ana';
        $b = new \stdClass();
        $b->nome = 'Bia';
        $tpl = '<!--[loop items]-->{items->nome},<!--[/loop items]-->';
        $this->assertSame('Ana,Bia,', $p->parse($tpl, ['items' => [$a, $b]]));
    }

    public function testParseIf(): void
    {
        $p = new TTemplateParser();
        $this->assertSame('oi Ana', $p->parse('{if nome}oi {nome}{endif}', ['nome' => 'Ana']));
        $this->assertSame('', $p->parse('{if nome}oi{endif}', ['nome' => '']));
        $this->assertSame('', $p->parse('{if missing}oi{endif}', []));
    }

    public function testRenderWithoutLayout(): void
    {
        $p = new TTemplateParser();
        $this->assertSame('Hi Ana', $p->render('Hi {nome}', ['nome' => 'Ana']));
    }

    public function testRenderWithLayout(): void
    {
        $p = new TTemplateParser();
        $p->section('title', 'Titulo');
        $out = $p->render('corpo {x}', ['x' => '1'], 'H:{block:title}|B:{block:content}');
        $this->assertSame('H:Titulo|B:corpo 1', $out);
    }

    public function testRenderMissingBlockIsEmpty(): void
    {
        $p = new TTemplateParser();
        $out = $p->render('corpo', [], 'A{block:missing}B');
        $this->assertSame('AB', $out);
    }
}
