<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;

interface BalanceRepositoryInterface
{
    /**
     * Получить баланс по адресу
     * @param string $address
     * @return Collection
     */
    public function getBalanceByAddress(string $address): Collection;
}