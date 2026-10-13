<?php
declare(strict_types=1);

namespace Aurea\Gen;

/**
 * TCrudGenerator — gera código-fonte de controllers Form/List
 * a partir de um modelo e da descrição dos campos.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TCrudGenerator
{
    /**
     * Cria o gerador para um modelo.
     *
     * @param string $model Classe do Active Record (ex: Cliente::class)
     * @param string $database Nome da config em config
     */
    public function __construct(
        protected string $model,
        protected string $database = 'app'
    ) {
    }

    /**
     * Gera o controller de formulário.
     *
     * @param string $class Nome do controller (ex: ClienteForm)
     * @param array $fields Lista de campos [['name','label','type','required']]
     * @return string Código PHP do controller
     */
    public function generateForm(string $class, array $fields): string
    {
        $short = $this->short($this->model);
        $formName = 'form_' . strtolower($short);
        $build = '';
        $vars = '';
        foreach ($fields as $f) {
            $name = $f['name'];
            $label = $f['label'] ?? $name;
            $type = $f['type'] ?? 'entry';
            $var = '$' . $name;
            $vars .= "        {$var} = new " . $this->fieldClass($type) . "('{$name}');\n";
            if (!empty($f['required'])) {
                $vars .= "        {$var}->addValidation('{$label}', new TRequiredValidator);\n";
            }
            $build .= "        \$this->form->addFields([new TLabel('{$label}')], [{$var}]);\n";
        }
        return <<<PHP
<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\\{$short};
use Aurea\Base\TStandardFormTrait;
use Aurea\Container\TVBox;
use Aurea\Core\TAction;
use Aurea\Core\TPage;
use Aurea\Form\TEmail;
use Aurea\Form\TEntry;
use Aurea\Form\TFormBuilder;
use Aurea\Form\TLabel;
use Aurea\Form\TNumber;
use Aurea\Form\TTextArea;
use Aurea\Validator\TRequiredValidator;

class {$class} extends TPage
{
    protected \$form;
    use TStandardFormTrait;

    public function __construct()
    {
        parent::__construct();
        \$this->setDatabase('{$this->database}');
        \$this->setActiveRecord({$short}::class);

        \$this->form = new TFormBuilder('{$formName}');
        \$this->form->setFormTitle('{$short}');

{$vars}
{$build}
        \$this->form->addAction('Salvar', new TAction([\$this, 'onSave']), 'fa-save');
        \$this->form->addActionLink('Novo', new TAction([\$this, 'onEdit']), 'fa-eraser');

        \$box = new TVBox;
        \$box->add(\$this->form);
        parent::add(\$box);
    }

    /** @param array \$param Requisição */
    public function onShow(array \$param = []): void
    {
        \$this->onEdit(\$param);
    }
}

PHP;
    }

    /**
     * Gera o controller de listagem.
     *
     * @param string $formClass Controller do formulário (para o botão Editar)
     * @param string $listClass Nome do controller de listagem
     * @param array $fields Lista de campos [['name','label']]
     * @return string Código PHP do controller
     */
    public function generateList(string $formClass, string $listClass, array $fields): string
    {
        $short = $this->short($this->model);
        $filterName = 'filter_' . strtolower($short);
        $filterFields = var_export(array_column($fields, 'name'), true);
        $cols = '';
        foreach ($fields as $f) {
            $cols .= "        \$this->grid->addColumn((new TColumn('{$f['name']}', '{$f['label']}'))->setOrderable());\n";
        }
        return <<<PHP
<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\\{$short};
use Aurea\Base\TStandardListTrait;
use Aurea\Container\TPanel;
use Aurea\Container\TVBox;
use Aurea\Core\TAction;
use Aurea\Core\TPage;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;
use Aurea\Dialog\TMessage;
use Aurea\Form\TEntry;
use Aurea\Form\TForm;
use Aurea\Grid\TColumn;
use Aurea\Grid\TDataGrid;
use Aurea\Grid\TPageNavigation;

class {$listClass} extends TPage
{
    use TStandardListTrait;

    protected Form \$filterForm;
    protected DataGrid \$grid;

    public function __construct()
    {
        parent::__construct();
        \$this->setDatabase('{$this->database}');
        \$this->setActiveRecord({$short}::class);
        \$this->setFilterFields({$filterFields});

        \$this->filterForm = new TForm('{$filterName}');
        \$this->filterForm->setProperty('class', 'd-flex gap-2 mb-3');
        \$this->grid = new TDataGrid;
{$cols}
        \$this->grid->addAction('Editar', new TAction(['{$formClass}', 'onEdit']), 'fa-pen', 'btn-outline-primary');
        \$this->grid->addAction('Excluir', new TAction([\$this, 'onDelete']), 'fa-trash', 'btn-outline-danger');

        \$panel = new TPanel('{$short}');
        \$panel->addToBody(\$this->filterForm);
        \$panel->addToBody(\$this->grid);

        \$box = new TVBox;
        \$box->add(\$panel);
        parent::add(\$box);
    }

    /** @param array \$param Requisição */
    public function onShow(array \$param = []): void
    {
        \$this->onReload(\$param);
    }

    /** @param array \$param Requisição */
    public function onReload(array \$param = []): void
    {
        try {
            Transaction::open(\$this->database);
            \$criteria = \$this->buildCriteria(\$param);
            \$this->grid->setItems((new Repository(\$this->activeRecord))->load(\$criteria));
            \$this->filterForm->setData(\$param);
            Transaction::close();
        } catch (\\Throwable \$e) {
            (new Message('error', \$e->getMessage()))->show();
            Transaction::rollback();
        }
    }
}

PHP;
    }

    /**
     * Mapeia o tipo lógico do campo para a classe de widget.
     *
     * @param string $type Tipo (entry, email, number, date, text)
     * @return string Classe do widget
     */
    protected function fieldClass(string $type): string
    {
        return match (strtolower($type)) {
            'email' => 'TEmail',
            'number' => 'TNumber',
            'date' => 'TDate',
            'text' => 'TTextArea',
            default => 'TEntry',
        };
    }

    /**
     * Extrai o nome curto da classe (sem namespace).
     *
     * @param string $class Nome completo da classe
     * @return string Nome curto
     */
    protected function short(string $class): string
    {
        $parts = explode('\\', ltrim($class, '\\'));
        return end($parts);
    }
}
