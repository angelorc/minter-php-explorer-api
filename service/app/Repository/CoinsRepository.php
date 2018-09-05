<?php

namespace App\Repository;


use App\Models\Coin;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class CoinsRepository implements CoinsRepositoryInterface
{


    /**
     * Get list of coins
     * @param array $filters
     * @return Collection
     */
    public function getList(array $filters = []): Collection
    {
        $query = Coin::query();

        if(isset($filters['symbol'])){
            $query->where('symbol', 'ilike', '%' .  $filters['symbol'] . '%');
        }

        return $query->get();
    }

    /**
     * Create coin from transaction
     *
     * @param Transaction $transaction
     * @return Coin
     */
    public function createCoinFromTransactions(Transaction $transaction): Coin
    {
        return Coin::updateOrCreate(['symbol' => $transaction->coin],
            [
                'name' => $transaction->name,
                'volume' => $transaction->initial_amount,
                'reserve_balance' => $transaction->initial_reserve,
                'crr' => $transaction->constant_reserve_ratio,
                'creator' => $transaction->from,
                'created_at' => $transaction->created_at,
            ]);
    }
}