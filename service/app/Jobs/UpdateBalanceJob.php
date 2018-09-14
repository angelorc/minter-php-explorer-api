<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Repository\BalanceRepository;
use App\Services\BalanceService;
use App\Services\MinterApiService;
use App\Traits\NodeTrait;
use Illuminate\Support\Collection;

class UpdateBalanceJob extends Job
{
    use NodeTrait;

    /** @var Collection */
    protected $transactions;

    public $queue = 'balance';

    /**
     * Create a new job instance.
     *
     * @param Collection $transactions
     */
    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $apiService = new MinterApiService($this->getActualNode());
        $centrifuge = new \phpcent\Client(env('CENTRIFUGE_URL', 'http://localhost:8000'));
        $centrifuge->setSecret(env('CENTRIFUGE_SECRET', null));
        $balanceService = new BalanceService(new BalanceRepository(), $centrifuge);

        $balances = collect([]);

        $this->transactions->each(function ($transaction) use (&$balances, $apiService, $balanceService) {
            /** @var Transaction $transaction */
            $data = $apiService->getAddressBalance($transaction->from);
            $balances = $balances->merge(
                $balanceService->updateAddressBalanceFromAipData($transaction->from, $data['balance']));

            if (isset($transaction->to) && $transaction->from !== $transaction->to) {
                $data = $apiService->getAddressBalance($transaction->to);
                $balances = $balances->merge(
                    $balanceService->updateAddressBalanceFromAipData($transaction->to, $data['balance']));
            }
        });

        $balanceService->broadcastNewBalances($balances);
    }
}
