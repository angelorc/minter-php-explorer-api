<?php

namespace App\Repository;

use App\Models\Balance;
use Illuminate\Database\Eloquent\Collection;

interface BalanceRepositoryInterface
{
    /**
     * Get balances by address
     * @param string $address
     * @return Collection
     */
    public function getBalanceByAddress(string $address): Collection;

    /**
     * Updete or create balance for address
     * @param string $address
     * @param string $coin
     * @param string $value
     * @return Balance
     */
    public function updateByAddressAndCoin(string $address, string $coin, string $value): Balance;
}