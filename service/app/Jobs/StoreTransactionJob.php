<?php

namespace App\Jobs;

use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Queue;

class StoreTransactionJob extends Job
{

    use TransactionTrait;

    public $queue = 'transactions';

    /** @var array */
    protected $transactionData;
    /** @var int */
    protected $blockHeight;
    /** @var \DateTime */
    protected $blockTime;
    /** @var bool */
    protected $shouldBroadcast;

    /**
     * Create a new job instance.
     *
     * @param array $transactionData
     * @param int $blockHeight
     * @param \DateTime $blockTime
     * @param bool $shouldBroadcast
     */
    public function __construct(array $transactionData, int $blockHeight, \DateTime $blockTime, bool $shouldBroadcast = true)
    {
        $this->transactionData = $transactionData;
        $this->blockHeight = $blockHeight;
        $this->blockTime = $blockTime;
        $this->shouldBroadcast = $shouldBroadcast;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $tx = $this->createTransactionFromApiData($this->transactionData, $this->blockHeight, $this->blockTime);
        if ($this->shouldBroadcast && $tx) {
            Queue::pushOn('broadcast_tx', new BroadcastTransactionJob($tx));
        }
    }

}
