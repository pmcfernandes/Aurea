# Guia para criar aplicações Aurea

Este repositório usa PHP 8.1+ e o framework Aurea. Estas regras aplicam-se à criação de qualquer aplicação nova com esta base.

## Princípios de trabalho

- Preserve alterações existentes; não use `git reset --hard`, `git checkout --` ou eliminações abrangentes.
- Antes de alterar código, leia os controladores, modelos e componentes relacionados.
- Use os componentes Aurea para construir a interface. Não introduza frameworks de frontend sem pedido explícito.
- Escreva a interface em português europeu quando esse for o idioma da aplicação.
- Formate PHP de forma legível: `declare(strict_types=1)`, imports organizados, blocos multilinha e sem várias instruções na mesma linha.
- Valide sempre a sintaxe dos ficheiros alterados com `php -l` e execute `git diff --check`.

## Estrutura recomendada

| Área | Localização | Responsabilidade |
| --- | --- | --- |
| Entrada HTTP | `public/index.php` | Carrega configuração, `.env`, autenticação, permissões e layout. |
| Controladores | `app/control/` | Páginas e eventos expostos. Namespace `App\Control`. |
| Modelos | `app/model/` | Active Records. Namespace `App\Model`. |
| Views | `app/views/` | HTML/PHP sem lógica de persistência. |
| Layout | `app/lib/`, `app/views/layouts/` | Shell autenticada e layout de convidado. |
| Menu | `app/menu.xml` | Navegação principal XML, quando aplicável. |
| Estilos | `public/assets/` | Um ficheiro CSS por página ou superfície quando necessário. |
| Configuração | `config/` | Conexões, autenticação e allow-list de permissões. |
| Migrações | `database/migrations/` | Esquema MariaDB versionado. |

## Criar uma página

1. Crie um controlador que estenda `Aurea\Core\TPage` ou o componente base adequado.
2. Registe CSS/JS no construtor com `TAssets::addCss()` e `TAssets::addJs()`.
3. Implemente `onShow(array $param = []): void`.
4. Carregue dados numa `TTransaction` e entregue-os a uma `TView`.
5. Registe o controlador e os eventos públicos em `config/application.php`.
6. Se for uma página de primeiro nível, acrescente-a ao `app/menu.xml`.

```php
final class ExamplePage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/example.css');
    }

    public function onShow(array $param = []): void
    {
        $this->addHtml((new TView('example/index'))
            ->set('items', [])
            ->fetch());
    }
}
```

## Base de dados e modelos

- Use MariaDB através de uma conexão definida em `config/`.
- As credenciais locais vêm de `.env` com variáveis `DB_*`. Nunca guarde credenciais reais em Git; mantenha `.env` ignorado e atualize apenas `.env.example` quando necessário.
- Crie migrações SQL em `database/migrations/` para tabelas, índices e chaves estrangeiras.
- Use Active Record e repositórios/modelos, nunca `SELECT`, `query()` ou `prepare()` diretamente em `app/`.
- Abra e feche sempre transações:

```php
TTransaction::open('app');
$records = ExampleRecord::where('status', '=', 'active')->get();
TTransaction::close();
```

- Em erro, use `TTransaction::rollback()`.
- Use `Model::where(...)->orderBy(...)->get()`, `->count()`, `->first()`, `->take()` e métodos de relação do modelo.
- Defina atributos, timestamps e relações explicitamente em cada `TRecord`.
- Normalize entradas de negócio antes de guardar com `setBeforeStore()`. Por exemplo, normalize valores monetários portugueses (`1.500,50`) para decimal.

## Segurança e permissões

- Todos os eventos HTTP públicos têm de estar na allow-list `permissions` de `config/application.php`.
- A proteção CSRF deve manter-se ativa. Formulários usam componentes que chamam `enableCsrf()` ou `TFormBuilder`/`TStandardForm`.
- Rotas autenticadas devem passar pelo callback de autenticação em `public/index.php`.
- Escape sempre conteúdo de dados na view: `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
- Para HTML controlado pela aplicação use `addHtml()`; para dados não confiáveis use `addText()`.
- Não exponha caminhos absolutos de uploads. Guarde caminhos relativos na base de dados.

## E-mail

- Use `Aurea\Mail\TMail`, baseado em PHPMailer, para enviar e-mails.
- Configure SMTP em `config/mail.php` através das variáveis `MAIL_*` do `.env`; nunca guarde palavras-passe SMTP no repositório.
- Defina destinatário, assunto, corpo HTML e corpo de texto alternativo antes de chamar `send()`.

## Formulários, CRUD e listagens

- Para CRUD normal, estenda `TStandardForm` e implemente `getDatabase()`, `getActiveRecord()` e `buildForm()`.
- Inclua o ID como `THidden('id')` e registe-o no `TForm`, para que uma edição não crie um novo registo.
- Use validadores Aurea (`TRequiredValidator`, `TEmailValidator`, etc.).
- Mantenha alertas e mensagens antes do formulário.
- Para listagens, estenda `TStandardList` e implemente filtro, grid, modelo e conexão.
- Defina ordenação padrão explícita com `setDefaultOrder('date_field', 'desc')` e limite os campos via `setOrderFields()` quando necessário.
- A exportação CSV de `TStandardList` é uma resposta de download direta. Links de exportação devem preservar filtros, mas nunca permitir que parâmetros substituam `class` ou `method`.
- Confirme que uma exportação responde com `Content-Type: text/csv`, `Content-Disposition: attachment` e sem layout HTML.

## Relações, tabs e modais

- Use tabs para organizar dados relacionados de uma entidade sem perder o contexto do formulário principal.
- Carregue relações pelo respetivo modelo e filtre pela chave estrangeira; não use SQL direto.
- Use `TWindow` para criar ou editar registos relacionados em modal quando o fluxo beneficiar de continuidade de contexto.
- Em modais, use **Guardar** como ação primária e **Fechar** como ação secundária.
- **Fechar**, X e backdrop devem apenas fechar o modal. Quando necessário, atualize a página de origem no evento `hidden.bs.modal` para recarregar as relações.
- Mantenha **Guardar** e **Fechar** no rodapé do modal. Se o submit vier de um formulário interno, use o atributo HTML `form` antes de mover o botão.
- Guarde uploads numa pasta sob `var/uploads/` e valide extensão e tamanho.

## Navegação, layout e design

- A shell autenticada deve ser composta por uma classe de layout e uma view em `app/views/layouts/`.
- A sidebar pode ler `app/menu.xml`; adicione entradas de primeiro nível nesse ficheiro e permita o controlador em `config/application.php`.
- Coloque o logout e os dados de sessão no rodapé da sidebar.
- A fonte global atual é **Inter**, carregada nos layouts por Google Fonts.
- `DESIGN.md` é a fonte de verdade para cores, tipografia, espaçamento, raios e componentes. Consulte-o antes de criar ou alterar a interface; não replique os seus valores neste ficheiro.
- Use CSS específico da página em `public/assets/<pagina>.css` e registe-o no controlador.
- Regras genéricas da shell podem sobrepor componentes baseados em `.card` e `.card-body`; use seletores mais específicos e, apenas se necessário, `!important` localizado.
- `public/index.php` usa `TAssets::setVersion()` para invalidação de cache. Aumente a versão ao alterar CSS/JS público relevante.

## Kanban

- Use `Aurea\Widget\TKanban`, não uma implementação manual.
- Crie colunas com `addColumn()` e cartões com `addCard()`.
- Configure `setMoveAction()` para persistir a nova fase/estado após drag-and-drop.
- Valide o identificador e a fase no evento de movimento e inclua esse evento nas permissões.
- O componente inclui SortableJS; não carregue uma segunda biblioteca de drag-and-drop.
- Preserve um espaçamento interno compacto nas listas Kanban (`8px`) para não criar margens excessivas entre cartões e colunas.

## Gráficos e indicadores

- Use `Aurea\Chart\TChart` para gráficos Chart.js.
- O componente suporta `bar`, `line`, `pie` e `doughnut`; defina labels, datasets e altura.
- Calcule indicadores a partir de modelos e agregue em PHP quando o domínio já usa Active Record; evite SQL direto na camada `app/`.
- Apresente sempre estados vazios e valores nulos de forma segura.

## Verificação manual

Após alterações relevantes:

1. Execute `php -l` para todos os PHP alterados.
2. Execute `git diff --check`.
3. Abra a página afetada com sessão autenticada.
4. Confirme erros no console e pedidos de CSS/JS com estado 200.
5. Teste os fluxos reais afetados: guardar, editar, modal, fechar, upload, ordenação, CSV, drag-and-drop ou gráfico.

## Comandos úteis

```powershell
# Validar todos os ficheiros da aplicação
Get-ChildItem app -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

# Verificar espaços e conflitos de patch
git diff --check

# Iniciar servidor local
php -S 127.0.0.1:8097 -t public
```
