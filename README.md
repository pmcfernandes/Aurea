# Aurea Framework

Framework PHP **baseada em Componentes e orientada a Eventos**.

> Telas são **compostas por componentes** (`TForm`, `TEntry`, `TDataGrid`, `TPanel`, …)
> e a interação acontece por **eventos** encapsulados em `TAction([Controller, método], params)`
> despachados por um **Front Controller** (`index.php?class=…&method=…`).

## Componentes

| Classe | Papel |
|---|---|
| `Aurea\Core\TComponent` | base de todo componente visual |
| `Aurea\Core\TAction` | encapsula evento controller+método+params |
| `Aurea\Core\TPage` | contentor de tela |
| `Aurea\Core\TApplication` | Front Controller |
| `Aurea\Form\TForm` / `TFormBuilder` | formulários |
| `Aurea\Form\TEntry`, `TCombo`, `TTextArea`, `TDate`, … | campos |
| `TField::addValidation()` + `Aurea\Validator\*` | validação |
| `TVBox`, `THBox`, `TPanel`, `TTable`, `TTabs` | contentores |
| `Aurea\Grid\TDataGrid` + `TColumn` | listagens |
| `Aurea\Dialog\TMessage`, `TQuestion`, `TToast` | diálogos |
| `Aurea\Database\TRecord` | Active Record |
| `TRepository`, `TCriteria`, `TFilter` | consultas |
| `Aurea\Database\TTransaction` | transações PDO |
| `Aurea\Base\TStandardFormTrait`, `TStandardListTrait` | CRUD padrão |
| `Aurea\Core\TScript` | injeta JS dos controllers (`create`, `redirect`, modais) |
| `Aurea\Core\TSession` | sessão com prefixo (`setValue`, `getValue`, `free`) |
| `Aurea\Core\TTranslator`, `_t()` | i18n pt/en + dicionários |
| `Aurea\Util\TDateTimeUtil` | conversões `dd/mm/yyyy` ⇄ `yyyy-mm-dd`, addDays, diff |
| `Aurea\Form\TDBCombo`, `TDBUniqueSearch` | campos vindos do banco |
| `Aurea\Form\TFileField` + `TUploader` | upload de arquivos |
| `Aurea\Log\TTxtLogger`, `THtmlLogger`, `TDbLogger` | logs + `TTransaction::setLogger()` |
| `Aurea\Container\TMenu::fromXml()` | menu com permissões |
| `Aurea\Dialog\TWindow` | modal Bootstrap via controller |
| `Aurea\Chart\TChart` | bar/line/pie/doughnut (Chart.js) |
| `ClienteApi` + `?format=json` | controllers que devolvem arrays → JSON |
| `Aurea\Database\TSqlSelect/Insert/Update/Delete` | query builder com prepared statements |
| `TCriteria::add($f, 'OR')`, `orWhere()` | filtros compostos AND/OR |
| `Aurea\Core\TConfig` | config `.php`/`.ini` com chaves em pontos |
| `Aurea\Template\TTemplateParser` | `{var}`, `{obj->attr}`, loops, `{if}` |
| `Aurea\Dialog\TInputDialog` | formulário dentro de modal |
| `Aurea\Form\TFieldList` | linhas editáveis mestre-detalhe |
| `TTime`, `TDateTimeField`, `TSlider`, `TColor`, `TSpinner`, `THtmlEditor`, `TMultiSearch`, `TSeekButton`, `TBarCode`, … | + `TMonthField`, `TCheckButton`, `TRadioButton`, `TIcon`, `TImageField` e variantes `DB*` |
| `Aurea\Http\*`, `Aurea\Service\TRestServer` | `HttpRequest/Response`, `TRestClient`, `TCookie`, servidor REST |
| `Aurea\IO\TFile` | ficheiros: ler/escrever/listar/apagar/download |
| `Aurea\Database\TDBLoop` | iteração de resultados (Iterator/Countable) |
| `Aurea\Base\TStandardForm/List/Seek` (abstratas) | CRUD pronto (a de Listagem traz CSV) |
| `Aurea\Auth\TPermission` | grants, checker, filtro de menu |
| `TExpander`, `TScroll`, `TFullCalendar`, `TKanban` | contentores e widgets |
| `TNIFValidator`, `TPostalCodeValidator` (PT) | NIF com dígito de controlo, CTT `0000-000` |
| `Aurea\Core\TCsrf` + `TForm::enableCsrf()` | token de sessão opt-in |
| `TColumn::setTotal()` + `tfoot` | `sum`, `count` ou callable |
| `Aurea\Gen\TCrudGenerator` | gera controllers Form/List a partir do modelo |
| `TMultiFileField`, `TSortableList`/`TDBSortableList`, `TMultiEntry`, `TTreeView`, `TDropDown` | upload múltiplo, ordenáveis, tags, árvore, dropdown |
| `TDataGrid::enableSelection()`, `TRowActionGroup` | checkboxes + dropdown de ações por linha |
| `TXmlLogger`, `TStyle`, `TFrame`, `TCard` | fecha a família de loggers + contentores |
| `TTimeline`, `TQrCode`, `TImageCropper` | linha do tempo, QR, crop (CDN) |
| `TSearchField`, `display` em ações, `TExpression` | pesquisa em tabelas grandes, `UPPER()` nos filtros |
| `TMigrator`, `TCsvImporter`, `TGridExporter`, `TCaptchaField` | versões de banco, import/export, anti-spam |
| `TStatCard`, `TStepForm`, `TSidebar`, `{block}` | dashboard, multi-passo, herança de templates |
| `TDataGridWrapper` | título + busca rápida + ações de cabeçalho |
| `THash::make/verify`, `TImageUtil::thumbnail()` | BCRYPT + resize com fallback |
| `app/model`, `app/control`, `app/Mvc`, `config/*.php` | estrutura da app |

## Início rápido

```bash
composer install
php init_db.php          # cria database/app.db (SQLite de exemplo)
php -S localhost:8000 -t public
```

Abrir:

- `http://localhost:8000/` — início
- `http://localhost:8000/index.php?class=ClienteForm&method=onShow` — formulário
- `http://localhost:8000/index.php?class=ClienteList&method=onReload` — listagem

## Conceitos

### 1. Componentes

Tudo é um `TComponent` (tag + atributos + filhos) que renderiza com `show()` / `getContents()`:

```php
use Aurea\Container\TVBox;
use Aurea\Container\TPanel;
use Aurea\Form\TEntry;
use Aurea\Form\TLabel;

$nome = (new TEntry('nome'))->setPlaceholder('Nome completo');
$box = new TVBox;
$box->add($nome);
$box->show();
```

### 2. Eventos (TAction)

A interação é um evento serializável para URL:

```php
use Aurea\Core\TAction;

$save = new TAction(['ClienteForm', 'onSave']);
$edit = new TAction(['ClienteForm', 'onEdit'], ['key' => 1]);

echo $edit->serialize();
// index.php?class=ClienteForm&method=onEdit&key=1

$form->addAction('Salvar', $save, 'fa-save');           // submit do form p/ o evento
$grid->addAction('Editar', $edit, 'fa-pen');            // botão por linha
$btn->setAction($edit);                                 // onclick="window.location=..."
```

No servidor, o `TApplication` cria o controller e chama o método com `$param`
(GET+POST):

```php
// index.php — Front Controller
TApplication::setDefault('HomeView', 'onShow');
TApplication::run(); // ?class=ClienteList&method=onReload
```

### 3. Dados (Active Record)

```php
use App\Model\Cliente;
use Aurea\Database\TTransaction;
use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Database\TRepository;

class Cliente extends TRecord {
    const TABLENAME = 'cliente';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial'; // ou 'max'
}

TTransaction::open('app');           // config/app.php

$c = new Cliente;
$c->fromArray(['nome' => 'Ana', 'email' => 'ana@example.com']);
$c->store();                        // INSERT … ou UPDATE se já tem id

$ana = Cliente::find(1);            // SELECT por PK
$lista = Cliente::where('cidade', '=', 'Sintra')->orderBy('nome')->get();

$criteria = (new TCriteria)->add(new TFilter('nome', 'LIKE', '%a%'));
$total = (new TRepository(Cliente::class))->count($criteria);

TTransaction::close();               // commit (ou rollback() no catch)
```

### 4. CRUD padrão (traits)

```php
use Aurea\Base\TStandardFormTrait;
use Aurea\Core\TPage;

class ClienteForm extends TPage {
    use TStandardFormTrait;
    public function __construct() {
        $this->setDatabase('app');
        $this->setActiveRecord(Cliente::class);
        // ... monta $this->form ...
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa-save');
    }
}
// ganha onSave($param) e onEdit($param) prontos
```

## Estrutura

```
bootstrap.php             bootstrap 
public/                   htaccess, robots.txt
app/                      pasta da app
config/                   conectores PDO
database/                 SQLite de exemplo
lib/Aurea/                framework 
tests/Unit/<Area>/        PHPUnit
```

## Recursos avançados

```php
use Aurea\Core\TScript;
use Aurea\Util\TDateTimeUtil;
use Aurea\Form\TDBCombo;
use Aurea\Log\TTxtLogger;
use Aurea\Database\TTransaction;

// JS a partir do controller (como TScript::create)
TScript::create("alert('salvo');");
TScript::redirect(new TAction(['ClienteList', 'onReload']));

// datas com máscara
TDateTimeUtil::convertToMask('25/12/2026', 'dd/mm/yyyy', 'yyyy-mm-dd'); // 2026-12-25

// combo vindo do banco (como TDBCombo)
$cidade = new TDBCombo('cidade_id', 'app', Cidade::class, 'id', 'nome', 'nome');

// auditoria SQL (como TTransaction::setLogger)
TTransaction::setLogger(new TTxtLogger('app/log/sql.log'));

// tradução (como _t())
echo _t('Record saved'); // "Registro salvo"
```

API JSON — o controller devolve array e o Front Controller serializa:

```
index.php?class=ClienteApi&method=onList&format=json
index.php?class=ClienteApi&method=onGet&format=json&key=1
```

TMenu via `app/menu.xml` (com callback de permissões em `TApplication::setMenuXml()`),
modal com `new TWindow('Título')`, gráficos com `new TChart('bar', 'Título')`,
login de exemplo em `LoginForm` (admin/admin) + `TAuth::requireLogin()`.

```php
use Aurea\Database\TSqlSelect;
use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Core\TConfig;
use Aurea\Template\TTemplateParser;

// query builder
$sel = (new TSqlSelect)->setEntity('cliente')->addColumn('*');
$sel->setCriteria((new TCriteria)->add(new TFilter('nome', 'LIKE', '%a%'), 'AND')
                                   ->orWhere('cidade', '=', 'Lisboa'));
$rows = $sel->fetchAll($pdo);

// config .php/.ini
$db = TConfig::load('config/app.php')->get('type'); // sqlite

// templates
echo (new TTemplateParser)->parse('Olá {nome}!', ['nome' => 'Ana']);

// grelha mestre-detalhe + modal com form + CSRF opt-in
$itens = new TFieldList('itens');
$dlg = new TInputDialog('Escolha');
$form->enableCsrf();

// servidor REST
$server = new TRestServer;
$server->addService('cliente', ClienteApi::class);
echo $server->handle();
```

## MVC clássico (opt-in, URLs amigáveis)

```php
use Aurea\Mvc\TRouter;

$router = new TRouter('App\\Mvc\\');
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');          // home/show => HomeController::onShow
$router->add('{controller}/{id:\d+}/{action}'); // posts/42/show
$router->dispatchFromRequest();                 // usa ?url= ou o path
```

```php
namespace App\Mvc;

use Aurea\Mvc\TController;

class HomeController extends TController
{
    public function onShow(): string
    {
        return $this->view('home/show', ['name' => $this->route_params['name'] ?? '?'])->fetch();
    }
}
```

Vistas em `app/views/<template>.php` (PHP normal com as variáveis extraídas).
Ver exemplo a correr em `public/mvc.php` (+ `public/.htaccess` para Apache).
```

## Testes (PHPUnit, 767 testes, 1675 asserções)

```bash
php vendor/bin/phpunit --testsuite Unit            # suite completa
php vendor/bin/phpunit --filter Tests.Unit.Database  # só uma área
```

Os testes vivem em `tests/Unit/<Area>/TClasseTest.php` (um TestCase por
classe de `lib/`), sem rede nem dependências externas.

## Notas

- Implementação original com **arquitetura** baseada em componentes,
  eventos e Active Record, com código 100% próprio.
- Suporta SQLite, MySQL, PostgreSQL, SQL Server, Oracle e Firebird via PDO.
- Requer PHP >= 8.1.
