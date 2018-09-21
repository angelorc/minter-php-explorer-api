<?php

namespace App\Services;


use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionServiceInterface
{
    /**
     * Store transactions to DB
     * @param array $data
     * @return Collection
     */
    public function createFromAipData(array $data): Collection;

    /**
     * @param array $data
     */
    public function createFromAipDataAsync(array $data): void;

    /**
     * @param array $txData
     * @param int $blockHeight
     * @param \DateTime $blockTime
     * @return Transaction|null
     */
    public function createTransactionFromApiData(array $txData, int $blockHeight, \DateTime $blockTime): ?Transaction;

    /**
     * Get total transactions count
     * @param string $address
     * @return int
     */
    public function getTotalTransactionsCount(string $address = null): int;

    /**
     * Get transactions count in 24h
     * @return int
     */
    public function get24hTransactionsCount(): int;

    /**
     * Transactions per second
     * @return float
     */
    public function getTransactionsSpeed(): float;

    /**
     * Get commission in period
     * @param \DateTime $startTime
     * @return string
     */
    public function getCommission(\DateTime $startTime = null): string;

    /**
     * Get average commission in period
     * @param \DateTime $startTime
     * @return string
     */
    public function getAverageCommission(\DateTime $startTime = null): string;


    /**
     * @return array
     */
    public function get24hTransactionsData(): array;

    /**
     * Store transaction tags
     * @param array $txTags
     */
    public function saveTransactionsTags(array $txTags): void;
}