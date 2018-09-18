<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Models\Transaction;
use App\Services\BalanceServiceInterface;
use App\Services\MinterApiService;
use App\Services\MinterApiServiceInterface;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

class UpdateBalanceJob extends Job
{
    use NodeTrait;

    public $queue = 'balance';

    /** @var Collection */
    protected $transactions;

    /** @var BalanceServiceInterface */
    protected $balanceService;

    /** @var MinterApiServiceInterface */
    protected $apiService;


    /**
     * Create a new job instance.
     *
     * @param Collection $transactions
     */
    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;

        $this->balanceService = app(BalanceServiceInterface::class);

        $this->apiService = new MinterApiService($this->getActualNode());
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $balances = collect([]);
        $this->transactions->each(function ($transaction) use (&$balances) {
            try {
                /** @var Transaction $transaction */
                $data = $this->apiService->getAddressBalance($transaction->from);
                $balances = $balances->merge(
                    $this->balanceService->updateAddressBalanceFromAipData($transaction->from, $data['balance']));
                if (isset($transaction->to) && $transaction->from !== $transaction->to) {
                    $data = $this->apiService->getAddressBalance($transaction->to);
                    $balances = $balances->merge(
                        $this->balanceService->updateAddressBalanceFromAipData($transaction->to, $data['balance']));
                }
            } catch (GuzzleException $exception) {
                LogHelper::apiError($exception);
            } catch (\Exception $exception) {
                LogHelper::error($exception);
            }
        });

        $this->balanceService->broadcastNewBalances($balances);
    }
}
