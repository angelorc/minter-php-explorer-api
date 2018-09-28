<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Services\BalanceServiceInterface;
use App\Services\MinterApiService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Queue;

class UpdateBalanceJob extends Job
{
    use NodeTrait;

    public $queue = 'balance';

    /** @var string */
    protected $address;

    /** @var BalanceServiceInterface */
    protected $balanceService;

    /**
     * Create a new job instance.
     *
     * @param string $address
     */
    public function __construct(string $address)
    {
        $this->address = $address;
        $this->balanceService = app(BalanceServiceInterface::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $apiService = new MinterApiService($this->getActualNode());

        try {
            $data = $apiService->getAddressBalance($this->address);
            $balances = $this->balanceService->updateAddressBalanceFromAipData($this->address, $data['balance']);
            Queue::pushOn('broadcast-balance', new BroadcastBalanceJob($balances));
        } catch (GuzzleException $exception) {
            LogHelper::apiError($exception);
        } catch (\Exception $exception) {
            LogHelper::error($exception);
        }
    }
}
