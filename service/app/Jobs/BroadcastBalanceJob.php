<?php

namespace App\Jobs;

use App\Services\BalanceService;
use App\Services\BalanceServiceInterface;
use Illuminate\Support\Collection;

class BroadcastBalanceJob extends Job
{
    public $queue = 'broadcast-balance';

    /** @var BalanceService */
    protected $balanceService;

    /** @var Collection */
    protected $balances;

    /**
     * Create a new job instance.
     *
     * @param Collection $balances
     */
    public function __construct(Collection $balances)
    {
        $this->balances = $balances;
        $this->balanceService = app(BalanceServiceInterface::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $this->balanceService->broadcastNewBalances($this->balances);
    }
}
