<?php

namespace App\Console\Commands;

use App\Models\TxPerDay;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @property TransactionRepositoryInterface transactionRepository
 */
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
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;


    /**
     * FillTxPerDayTableCommand constructor.
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        parent::__construct();

        $this->transactionRepository = $transactionRepository;
    }

    /**
     *
     * @throws \RuntimeException
     */
    public function handle(): void
    {
        TxPerDay::truncate();

        $today = new \DateTime();

        $dates = DB::table('blocks')
            ->select(DB::raw('timestamp::date'))
            ->distinct()
            ->orderBy('timestamp')
            ->pluck('timestamp');

        foreach ($dates as $date) {

            if ($date === $today->format('Y-m-d') ) {
                continue;
            }

            $count = $this->transactionRepository->getTransactionsPerDayCount(new \DateTime($date));

            $txCount = new TxPerDay();
            $txCount->date = $date;
            $txCount->transactions_count = $count;

            $txCount->save();

            $this->info("{$txCount->date}: {$txCount->transactions_count} txs");
        }
    }
}