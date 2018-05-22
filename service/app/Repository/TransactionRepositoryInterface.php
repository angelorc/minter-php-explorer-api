<?php

namespace App\Repository;


use App\Models\Block;
use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    /**
     * Сохранить транзакцию
     * @param Transaction $transaction
     */
    public function save(Transaction $transaction): void;

    /**
     * Найти транзакцию по Id
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction;

    /**
     * Найти блок по Id
     * @param string $hash
     * @return Transaction|null
     */
    public function findByHash(string $hash): ?Transaction;

    /**
     * Получить все транзакции
     * @param array $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAllQuery(array $filter = []) : \Illuminate\Database\Eloquent\Builder;

}