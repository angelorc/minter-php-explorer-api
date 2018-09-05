<?php

namespace App\Services;


use App\Models\Coin;
use App\Models\Transaction;

interface CoinServiceInterface
{
    /**
     * Create coin from transaction
     * @param Transaction $transaction
     * @return Coin
     */
    public function createCoinFromTransactions(Transaction $transaction): ?Coin;
}