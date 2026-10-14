<?php
declare(strict_types=1);

namespace Aurea\Service;

use Aurea\Database\TCriteria;
use Aurea\Database\TFilter;
use Aurea\Database\TRecord;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;

/**
 * Endpoint de pesquisa paginada para um único Active Record autorizado.
 *
 * A classe e os campos são definidos no construtor, nunca pelo pedido HTTP.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
final class TRecordSearchService
{
    /**
     * @param string $database Configuração de base de dados
     * @param class-string<TRecord> $model Modelo explicitamente autorizado
     * @param string $displayField Campo pesquisado e exibido
     * @param string $idField Campo devolvido como identificador
     * @param int $minLength Mínimo de caracteres para pesquisar
     * @param string|null $orderField Ordenação fixa opcional
     */
    public function __construct(
        private string $database,
        private string $model,
        private string $displayField,
        private string $idField = 'id',
        private int $minLength = 1,
        private ?string $orderField = null
    ) {
        if (!is_subclass_of($model, TRecord::class)) {
            throw new \InvalidArgumentException('O modelo de pesquisa deve estender TRecord.');
        }
    }

    /**
     * Pesquisa pelos parâmetros `q` ou `term`, com `page` e `limit` opcionais.
     * @param array<string,mixed> $param Parâmetros do pedido
     * @return array{data:array<int,array{id:mixed,text:mixed}>,total:int,page:int,per_page:int}
     */
    public function onSearch(array $param = []): array
    {
        $term = trim((string) ($param['q'] ?? $param['term'] ?? ''));
        $page = max(1, (int) ($param['page'] ?? 1));
        $limit = min(100, max(1, (int) ($param['limit'] ?? 10)));
        $empty = ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $limit];
        if (mb_strlen($term) < $this->minLength) {
            return $empty;
        }
        TTransaction::open($this->database);
        try {
            $criteria = (new TCriteria)
                ->add(new TFilter($this->displayField, 'LIKE', '%' . $term . '%'))
                ->setProperty('limit', $limit)
                ->setProperty('offset', ($page - 1) * $limit);
            if ($this->orderField !== null) {
                $criteria->setProperty('order', $this->orderField);
            }
            $repository = new TRepository($this->model);
            $data = [];
            foreach ($repository->load($criteria) as $record) {
                $row = $record->toArray();
                $data[] = ['id' => $row[$this->idField] ?? null, 'text' => $row[$this->displayField] ?? ''];
            }
            $count = $repository->count((new TCriteria)->add(new TFilter($this->displayField, 'LIKE', '%' . $term . '%')));
            TTransaction::close();
            return ['data' => $data, 'total' => $count, 'page' => $page, 'per_page' => $limit];
        } catch (\Throwable $e) {
            TTransaction::rollback();
            throw $e;
        }
    }
}
