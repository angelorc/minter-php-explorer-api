<?php

namespace App\Repository;


use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        if($filter['addresses']){
            $addresses = implode(',', array_map(function($item){
                return "'" .   preg_replace("/\W/", '', $item)  .  "'";}, $filter['addresses']));

            $query->where(function ($query) use ($addresses){
                $query
                    ->whereRaw('transactions.from ilike any (array['.$addresses.']) ')
                    ->orWhereRaw('transactions.to ilike any (array['.$addresses.']) ');
            });

        }
        elseif($filter['address']){
            $query->where(function ($query) use ($filter){
                $query->where('transactions.from', 'ilike', $filter['address'])
                    ->orWhere('transactions.to', 'ilike', $filter['address']);
            });
        }

        return $query;
    }

    /**
     * Получить количество транзакций за сутки
     * Если дата не передается, возвращается количество за предыдущие сутки
     * @param \DateTime|null $dateTime
     * @return int
     * @throws \Exception
     */
    public function getTransactionsPerDayCount(\DateTime $dateTime = null): int
    {
        if (!$dateTime){
            $dt = new \DateTime();
            $date = $dt->sub(new \DateInterval('PT24H'));
        }else{
            $date = $dateTime->format('Y-m-d');
        }

        $query = "
                WITH tx_per_day AS (
                    select count(t.id) as tx_count
                    from blocks b
                      left join transactions t on b.id = t.block_id
                    where b.timestamp::date = '{$date}'
                    group by b.id
                )
                select sum(tx_count) as cnt from tx_per_day;
            ";
        $txs = DB::selectOne($query);

        return $txs->cnt ?? 0;
    }
}