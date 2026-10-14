<?php
declare(strict_types=1);

namespace Aurea\Form;

use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;

/**
 * TDBEntry — entrada de texto com autocomplete vindo do banco.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TDBEntry extends TEntry
{
    protected ?TCriteria $criteria = null;
    protected int $minLength = 1;

    /**
     * Cria a entrada com origem no banco de dados.
     *
     * @param string $name Nome do campo
     * @param string $database Nome do banco de dados
     * @param string $model Classe do modelo
     * @param string $display Coluna exibida nas sugestões
     * @param string|null $orderColumn Coluna de ordenação
     */
    public function __construct(
        string $name,
        protected string $database = '',
        protected string $model = '',
        protected string $display = '',
        protected ?string $orderColumn = null
    ) {
        parent::__construct($name);
    }

    /**
     * Define o critério de busca das sugestões.
     *
     * @param TCriteria $criteria Critério de busca
     * @return static
     */
    public function setCriteria(TCriteria $criteria): static
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * Define o mínimo de caracteres para pesquisar.
     *
     * @param int $length Mínimo de caracteres
     * @return static
     */
    public function setMinLength(int $length): static
    {
        $this->minLength = max(1, $length);
        return $this;
    }

    /**
     * Pesquisa sugestões para o termo informado.
     *
     * @return array Lista chave => descrição
     */
    public function search(string $term, int $limit = 10): array
    {
        return $this->searchPage($term, 1, $limit)['data'];
    }

    /**
     * Pesquisa sugestões paginadas, sem expor metadados ou campos do modelo.
     *
     * @param string $term Termo a pesquisar
     * @param int $page Página iniciada em um
     * @param int $limit Máximo de resultados por página (limitado a 100)
     * @return array{data:array<int,array{id:mixed,text:mixed}>,total:int,page:int,per_page:int}
     */
    public function searchPage(string $term, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = min(100, max(1, $limit));
        $empty = ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $limit];
        if ($this->database === '' || $this->model === '' || $this->display === '') {
            return $empty;
        }
        $term = trim($term);
        if (mb_strlen($term) < $this->minLength) {
            return $empty;
        }
        TTransaction::open($this->database);
        try {
            $criteria = $this->criteria ? clone $this->criteria : new TCriteria;
            $criteria->add(new TFilter($this->display, 'LIKE', '%' . $term . '%'));
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('offset', ($page - 1) * $limit);
            if ($this->orderColumn) {
                $criteria->setProperty('order', $this->orderColumn);
            }
            $countCriteria = $this->criteria ? clone $this->criteria : new TCriteria;
            $countCriteria->add(new TFilter($this->display, 'LIKE', '%' . $term . '%'));
            $repository = new TRepository($this->model);
            $items = [];
            foreach ($repository->load($criteria) as $obj) {
                $arr = $obj->toArray();
                $items[] = ['id' => $arr['id'] ?? null, 'text' => $arr[$this->display] ?? ''];
            }
            $total = $repository->count($countCriteria);
            TTransaction::close();
            return ['data' => $items, 'total' => $total, 'page' => $page, 'per_page' => $limit];
        } catch (\Throwable) {
            TTransaction::rollback();
            return $empty;
        }
    }

    /**
     * Obtém o HTML do campo com datalist de sugestões.
     *
     * @return string HTML do campo
     */
    public function getContents(): string
    {
        $this->setProperty('data-db-search', '1');
        $this->setProperty('data-min-length', (string) $this->minLength);
        $this->setProperty('list', 'datalist_' . $this->getName());
        $html = parent::getContents();
        $html .= '<datalist id="datalist_' . htmlspecialchars($this->getName(), ENT_QUOTES) . '"></datalist>';
        return $html;
    }
}
