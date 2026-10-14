<?php
declare(strict_types=1);

namespace Aurea\Base;

use Aurea\Database\TRecord;

/**
 * Persistência transacional de linhas mestre-detalhe submetidas por TFieldList.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
trait TMasterDetailTrait
{
    /**
     * Substitui os detalhes do mestre pelos dados validados recebidos.
     * A transação deve estar aberta pelo chamador. Somente campos presentes em
     * $allowedFields são copiados das linhas submetidas.
     *
     * @param class-string<TRecord> $detailClass
     * @param array<int,array<string,mixed>> $rows
     * @param string[] $allowedFields
     * @throws \InvalidArgumentException Se a classe de detalhe não for um Active Record
     * @throws \RuntimeException Se o mestre ainda não estiver persistido
     */
    protected function replaceDetails(TRecord $master, string $detailClass, string $foreignKey, array $rows, array $allowedFields): void
    {
        if (!is_subclass_of($detailClass, TRecord::class)) {
            throw new \InvalidArgumentException('A classe de detalhe deve estender TRecord.');
        }
        $masterId = $master->getPrimaryKeyValue();
        if ($masterId === null) { throw new \RuntimeException('O mestre deve ser gravado antes dos detalhes.'); }
        $detailClass::where($foreignKey, '=', $masterId)->delete();
        foreach ($rows as $row) {
            $detail = new $detailClass;
            $detail->{$foreignKey} = $masterId;
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $row)) { $detail->{$field} = $row[$field]; }
            }
            $detail->store();
        }
    }
}
