<?php

namespace App\Jobs;

use App\Models\Block;
use App\Services\StatusServiceInterface;

/**
 * Class BroadcastStatusInfoJob
 * @package App\Jobs
 */
class BroadcastStatusInfoJob extends Job
{
    public $queue = 'broadcast';

    /** @var Block */
    protected $block;

    /** @var \phpcent\Client */
    protected $centrifuge;

    /** @var StatusServiceInterface */
    protected $statusService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->centrifuge = app(\phpcent\Client::class);
        $this->statusService = app(StatusServiceInterface::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $channel = env('MINTER_NETWORK', false) ? env('MINTER_NETWORK', 'mainnet') . '_status-info' : 'status-info';

        $this->centrifuge->publish($channel, $this->statusService->getStatusInfo());
    }
}
