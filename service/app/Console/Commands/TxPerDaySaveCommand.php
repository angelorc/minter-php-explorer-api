<?php

namespace App\Console\Commands;

use App\Models\TxPerDay;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @property TransactionRepositoryInterface transactionRepository
 */
class TxPerDaySaveCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'transactions:per_count_save';

    /**
     * @var string
     */
    protected $description = 'Save transactions per date count';

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
     * @throws \Exception
     */
    public function handle(): void
    {
        $count = $this->transactionRepository->getTransactionsPerDayCount();

        $date = new \DateTime();

        $txCount = new TxPerDay();
        $txCount->date = $date->sub(new \DateInterval('PT24H'))->format('Y-m-d');
        $txCount->transactions_count = $count;

        $txCount->save();

        $this->info("{$txCount->date}: {$txCount->transactions_count} txs");
    }
}