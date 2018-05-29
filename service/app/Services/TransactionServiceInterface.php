<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface TransactionServiceInterface
{
    /**
     * Получить колекцию транзакций из данных API
     * @param array $data
     * @return Collection
     */
    public function decodeTransactionsFromApiData(array $data): Collection;

    /**
     * Количество транзакций
     * @return int
     */
    public function getTotalTransactionsCount(): int;

    /**
     * Количество транзакций за последние 24 часа
     * @return int
     */
    public function get24hTransactionsCount(): int;

    /**
     * Скорость обработки транзакций
     * @return float
     */
    public function getTransactionsSpeed(): float;
}