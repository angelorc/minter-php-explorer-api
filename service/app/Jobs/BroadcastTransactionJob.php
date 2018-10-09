<?php

namespace App\Jobs;

use App\Http\Resources\TransactionResource;
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
     * @param Transaction $transaction
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
        $channel = env('MINTER_NETWORK', false) ? env('MINTER_NETWORK', 'mainnet') . '_transactions' : 'transactions';

        $this->centrifuge->publish($channel, new TransactionResource($this->transaction));
    }
}
