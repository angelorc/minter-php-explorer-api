<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\CoinService;

class CreateCoinFromTransactionJob extends Job
{
    /** @var array */
    protected $transaction;
    /** @var CoinService */
    protected $coinService;

    public $queue = 'main';

    /**
     * Create a new job instance.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->coinService = app(CoinService::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $this->coinService->createCoinFromTransactions($this->transaction);
    }

}
