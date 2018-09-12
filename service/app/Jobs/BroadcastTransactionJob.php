<?php

namespace App\Jobs;

use App\Http\Resources\TransactionResource;
use App\Models\Block;
use App\Models\Transaction;

class BroadcastTransactionJob extends Job
{
    public $queue = 'broadcast';

    /** @var Transaction */
    protected $transaction;

    /** @var \phpcent\Client */
    protected $centrifuge;

    /**
     * Create a new job instance.
     *
     * @param Block $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->centrifuge = app(\phpcent\Client::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $this->centrifuge->publish('transactions', new TransactionResource($this->transaction));
    }
}
