<?php

namespace App\Repository;


use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    /**
     * Store transaction
     * @param Transaction $transaction
     * @param Collection|null $tags
     * @return Transaction
     */
    public function save(Transaction $transaction, Collection $tags = null): Transaction;

    /**
     * Find transactions by Id
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction;

    /**
     * Find by hash
     * @param string $hash
     * @return Transaction|null
     */
    public function findByHash(string $hash): ?Transaction;

    /**
     * Get all transactions
     * @param array $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAllQuery(array $filter = []) : \Illuminate\Database\Eloquent\Builder;

    /**
     * Get transactions count per day
     * Если дата не передается, возвращается количество за предыдущие сутки
     * @param \DateTime|null $date
     * @return int
     */
    public function getTransactionsPerDayCount(\DateTime $date = null): int;

    /**
     * Get total transactions count for address
     * @param string|null $address
     * @param string|null $address
     * @return int
     */
    public function getTransactionsCount(string $address = null): int;

    /**
     * Get transactions count in 24h
     * @return int
     */
    public function get24hTransactionsCount(): int;

    /**
     * Get transactions in 24h
     * @return Collection
     */
    public function get24hTransactions(): Collection;

    /**
     * Get average commission in 24h
     * @return Collection
     */
    public function get24hTransactionsAverageCommission(): string;


    /**
     * Get commission in 24h
     * @return Collection
     */
    public function get24hTransactionsCommission(): string;

    /**
     * Get summary transactions data
     * @return array
     */
    public function get24hTransactionsData(): array;

    /**
     * @param string $address
     * @return array
     */
    public function getDelegationsForAddress(string $address): array;

}