<?php

namespace App\Jobs;

use App\Services\StatusServiceInterface;
use App\Services\TransactionServiceInterface;
use Illuminate\Support\Facades\Cache;

class BroadcastStatusPageJob extends Job
{
    public $queue = 'broadcast';

    /** @var int */
    protected $validatorsCount;

    /** @var int */
    protected $candidatesCount;

    /** @var StatusServiceInterface */
    protected $statusService;

    /** @var TransactionServiceInterface */
    protected $transactionService;

    /** @var TransactionServiceInterface */
    protected $centrifuge;

    /**
     * Create a new job instance.
     *
     * @param int $validatorsCount
     * @param int $candidatesCount
     */
    public function __construct(int $validatorsCount, int $candidatesCount)
    {
        $this->validatorsCount = $validatorsCount;
        $this->candidatesCount = $candidatesCount;

        $this->statusService = app(StatusServiceInterface::class);
        $this->transactionService = app(TransactionServiceInterface::class);
        $this->centrifuge = app(\phpcent\Client::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {

        $status = Cache::get('explorer_status', false);

        if (!$status) {
            $status = $this->statusService->isActiveStatus() ? 'active' : 'down';
        }

        $transactionData = $this->transactionService->get24hTransactionsData();

        $data = [
            'status' => $status,
            'uptime' => $this->statusService->getUpTime() * 100,
            'numberOfBlocks' => $this->statusService->getLastBlockHeight(),
            'blockSpeed24h' => $this->statusService->getAverageBlockTime(),
            'txTotalCount' => $this->transactionService->getTotalTransactionsCount(),
            'tx24hCount' => $transactionData['count'],
            'txPerSecond' => $transactionData['perSecond'],
            'activeValidators' => $this->validatorsCount,
            'activeCandidates' => $this->candidatesCount,
            'averageTxCommission' => $transactionData['avg'],
            'totalCommission' => $transactionData['sum'],
        ];

        $channel = env('MINTER_NETWORK', false) ? env('MINTER_NETWORK', 'mainnet') . '_status_page' : 'status_page';

        $this->centrifuge->publish($channel, $data);
    }
}
