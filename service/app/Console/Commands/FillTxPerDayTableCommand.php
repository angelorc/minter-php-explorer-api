<?php

namespace App\Console\Commands;

use App\Models\TxPerDay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillTxPerDayTableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'transactions:fill_tx_count';

    /**
     * @var string
     */
    protected $description = 'Fill transactions per date table';

    /**
     *
     * @throws \RuntimeException
     */
    public function handle(): void
    {
        TxPerDay::truncate();

        $dates = DB::table('blocks')
            ->select(DB::raw('timestamp::date'))
            ->distinct()
            ->orderBy('timestamp')
            ->pluck('timestamp');

        foreach ($dates as $date) {

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

            $txCount = new TxPerDay();
            $txCount->date = $date;
            $txCount->transactions_count = $txs->cnt;

            $txCount->save();

            $this->info("{$txCount->date}: {$txCount->transactions_count} txs");
        }
    }
}