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

    /**
     * Получить количество транзакций за сутки
     * Если дата не передается, возвращается количество за предыдущие сутки
     * @param \DateTime|null $date
     * @return int
     */
    public function getTransactionsPerDayCount(\DateTime $date = null): int;

    /**
     * Количество транзакций
     * @param string|null $address
     * @return int
     */
    public function getTransactionsCount(string $address = null): int;

    /**
     * Получить количество транзакций за последние 24 часа
     * @return int
     */
    public function get24hTransactionsCount(): int;

}