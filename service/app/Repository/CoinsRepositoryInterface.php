<?php

namespace App\Repository;


use App\Models\Coin;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface CoinsRepositoryInterface
{
    /**
     * Create coin from transaction
     *
     * @param Transaction $transaction
     * @return Coin
     */
    public function createCoinFromTransactions(Transaction $transaction): Coin;

    /**
     * Get list of coins
     *
     * @param array $filters
     * @return Collection
     */
    public function getList(array $filters = []): Collection;

    /**
     * Total coins in network by coin
     * @return array
     */
    public function getTotalAmountByCoins(): array;
}