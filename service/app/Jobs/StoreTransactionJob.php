<?php

namespace App\Jobs;

use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Queue;

class StoreTransactionJob extends Job
{

    use TransactionTrait;

    /** @var array */
    protected $transactionData;
    /** @var int */
    protected $blockHeight;
    /** @var \DateTime */
    protected $blockTime;

    public $queue = 'transactions';

    /**
     * Create a new job instance.
     *
     * @param array $transactionData
     * @param int $blockHeight
     * @param \DateTime $blockTime
     */
    public function __construct(array $transactionData, int $blockHeight, \DateTime $blockTime)
    {
        $this->transactionData = $transactionData;
        $this->blockHeight = $blockHeight;
        $this->blockTime = $blockTime;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $tx = $this->createTransactionFromApiData($this->transactionData, $this->blockHeight, $this->blockTime);
        if ($tx) {
            Queue::pushOn('broadcast', new BroadcastTransactionJob($tx));
            Queue::pushOn('balance', new UpdateBalanceJob(collect([$tx])));
        }
    }

}
