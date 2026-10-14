<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Cliente;
use Aurea\Database\TCriteria;
use Aurea\Database\TRepository;
use Aurea\Database\TTransaction;

/** Fixture JSON mantida para os testes de dispatch do framework. */
class ClienteApi
{
    /** @return array{data:array<int,array<string,mixed>>,total:int,page:int,per_page:int} */
    public function onList(array $param = []): array
    {
        $page = max(1, (int) ($param['page'] ?? 1));
        $limit = max(1, min(100, (int) ($param['limit'] ?? 10)));
        TTransaction::open('app');
        try {
            $criteria = (new TCriteria)->setProperty('limit', $limit)->setProperty('offset', ($page - 1) * $limit);
            $repository = new TRepository(Cliente::class);
            $data = array_map(fn(Cliente $client): array => $client->toArray(), $repository->load($criteria));
            $total = $repository->count();
            TTransaction::close();
            return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $limit];
        } catch (\Throwable $error) {
            TTransaction::rollback();
            throw $error;
        }
    }
}
