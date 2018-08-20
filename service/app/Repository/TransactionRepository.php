<?php

namespace App\Repository;


use App\Models\Block;
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
     * Сохранить тэги транзакции
     * @param Transaction $transaction
     * @param Collection $tags
     */
    public function saveTransactionTags(Transaction $transaction, Collection $tags): void{
        $transaction->tags()->saveMany($tags);
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
        $transaction = Transaction::where('hash', 'ilike', $hash)->where('status', true)->first();

        if ($transaction) {
            return $transaction;
        }

        return Transaction::where('hash', 'ilike', $hash)->orderBy('created_at', 'asc')->first();
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
            $dt->modify('-1 day');
            $date = $dt->format('Y-m-d');
        } else {
            $date = $dateTime->format('Y-m-d');
        }

        return Transaction::whereDate('created_at', $date)->count();
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
        $dt = new \DateTime();
        $dt->modify('-1 day');
        //TODO: Возможно стоит брать транзакции на начало часа, что позволит кэшировать данные на час
        return Block::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))->sum('tx_count');
    }

    /**
     * Получить количество транзакций за последние 24 часа
     * @return string
     * @throws \Exception
     */
    public function get24hTransactionsAverageCommission(): string
    {
        $dt = new \DateTime();
        $dt->modify('-1 day');

        return Transaction::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))->avg('fee') ?? 0;

    }

    /**
     * Получить транзакции за последние 24 часа
     * @return Collection
     * @throws \Exception
     */
    public function get24hTransactions(): Collection
    {
        $dt = new \DateTime();
        $dt->modify('-1 day');

        return Transaction::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))->get();
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

        if (!empty($filter['addresses']) && \is_array($filter['addresses'])) {

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

        if (!empty($filter['hashes']) && \is_array($filter['hashes'])) {
            $hashes = implode(',', array_map(function ($item) {
                return "'" . preg_replace("/\W/", '', $item) . "'";
            }, $filter['hashes']));

            $query->where(function ($query) use ($hashes) {
                $query->whereRaw('transactions.hash ilike any (array[' . $hashes . ']) ');
            });

        } elseif (!empty($filter['hash'])) {
            $query->where(function ($query) use ($filter) {
                $query->where('transactions.hash', 'ilike', $filter['hash']);
            });
        }

        if (!empty($filter['pubKeys']) && \is_array($filter['pubKeys'])) {
            $keys = implode(',', array_map(function ($item) {
                return "'" . preg_replace("/\W/", '', $item) . "'";
            }, $filter['pubKeys']));

            $query->where(function ($query) use ($keys) {
                $query->whereRaw('transactions.pub_key ilike any (array[' . $keys . ']) ');
            });

        } elseif (!empty($filter['pubKey'])) {
            $query->where(function ($query) use ($filter) {
                $query->where('transactions.pub_key', 'ilike', $filter['pubKey']);
            });
        }

        return $query;
    }

    /**
     * Получить коммисию транзакции за последние 24 часа
     * @return Collection
     */
    public function get24hTransactionsCommission(): string
    {
        $dt = new \DateTime();
        $dt->modify('-1 day');

        return Transaction::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))->sum('fee');
    }

    /**
     * Данные по трнзакциям за 24 часа
     * @return array
     */
    public function get24hTransactionsData(): array
    {
        $dt = new \DateTime();
        $dt->modify('-1 day');

        $result = DB::select('
            select count(fee), sum(fee) as sum , avg(fee)  as avg
            from transactions
            where created_at >= :date ;
        ', ['date' => $dt->format('Y-m-d H:i:s')]);

        return [
            'count' => $result[0]->count ?? 0,
            'sum' => $result[0]->sum ?? 0,
            'avg' => $result[0]->avg ?? 0,
        ];
    }
}