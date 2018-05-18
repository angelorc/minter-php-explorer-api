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
}