<?php

namespace App\Repository;


use App\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{

    /**
     * Сохранить транзакцию
     * @param Transaction $transaction
     */
    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }

    /**
     * Найти транзакцию по Id
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    /**
     * Найти транзакцию по hash
     * @param string $hash
     * @return Transaction|null
     */
    public function findByHash(string $hash): ?Transaction
    {
        return Transaction::where('hash', $hash)->first();
    }

    /**
     * Получить все транзакции
     * @param array $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAllQuery(array $filter = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = Transaction::query();

        if ($filter['block']) {
            $query->whereHas('block', function ($query) use ($filter) {
                $query->where('blocks.height', $filter['block']);
            });
        }

        if($filter['account']){
            $query->where(function ($query) use ($filter){
                $query->where('transactions.from', 'ilike', $filter['account'])
                    ->orWhere('transactions.to', 'ilike', $filter['account']);
            });
        }

        return $query;
    }
}