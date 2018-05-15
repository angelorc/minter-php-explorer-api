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
     * @param int $page
     * @param array $filter
     * @return Collection
     */
    public function getAll(int $page = 1, array $filter = []): Collection
    {
        $query = Transaction::with('block');

        if ($filter['block_height']) {
            $query->whereHas('block', function ($query) use ($filter) {
                $query->where('block.height', $filter['block_height']);
            });
        }

        if($filter['account']){
            $query->where(function ($query) use ($filter){
                $query->where('transactions.from',  $filter['account'])
                    ->orWhere('transactions.to', $filter['account']);
            });
        }

        return $query->orderByDesc('id')->get();
    }
}