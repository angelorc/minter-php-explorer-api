<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface BalanceServiceInterface
{
    /**
     * Получить баланс адреса
     * @param string $address
     * @return Collection
     */
    public function getAddressBalance(string $address): Collection;
}