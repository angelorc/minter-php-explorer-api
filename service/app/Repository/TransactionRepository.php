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

        if (!empty($filter['block'])) {
            $query->whereHas('block', function ($query) use ($filter) {
                $query->where('blocks.height', $filter['block']);
            });
        }

        if (isset($filter['startTime'])) {
            $query->whereHas('block', function ($query) use ($filter) {
                $query->where('blocks.timestamp', '>=', $filter['startTime']);
            });
        }

        if (!empty($filter['addresses'])) {
            $addresses = implode(',', array_map(function ($item) {
                return "'" . preg_replace("/\W/", '', $item) . "'";
            }, $filter['addresses']));

            $query->where(function ($query) use ($addresses) {
                $query
                    ->whereRaw('transactions.from ilike any (array[' . $addresses . ']) ')
                    ->orWhereRaw('transactions.to ilike any (array[' . $addresses . ']) ');
            });

        } elseif (!empty($filter['address'])) {
            $query->where(function ($query) use ($filter) {
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
        if (!$dateTime) {
            $dt = new \DateTime();
            $date = $dt->sub(new \DateInterval('PT24H'))->format('Y-m-d');
        } else {
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

    /**
     * Количество транзакций
     * @param string|null $address
     * @return int
     */
    public function getTransactionsCount(string $address = null): int
    {
        $query = Transaction::query();

        if ($address) {
            $query->where('from', 'like', $address)
                ->orWhere('to', 'like', $address);
        }

        return $query->count();
    }

    /**
     * Получить количество транзакций за последние 24 часа
     * @return int
     * @throws \Exception
     */
    public function get24hTransactionsCount(): int
    {

        //TODO: Возможно стоит брать транзакции на начало часа, что позволит кэшировать данные на час
        $sql = "
            select count(t.id)
            from blocks as b
              join transactions t on b.id = t.block_id
            where b.timestamp::TIMESTAMP >= now() - '24 Hour'::INTERVAL;
        ";

        $result = DB::selectOne($sql);

        return $result->count ?? 0;
    }

    /**
     * Получить количество транзакций за последние 24 часа
     * @return Collection
     * @throws \Exception
     */
    public function get24hTransactions(): Collection
    {

        $dt = new \DateTime();
        $dt->sub(new \DateInterval('PT24H'));

        return $this->getAllQuery(['startTime' => $dt->format('Y-m-d H:i:s')])->get();
    }

}