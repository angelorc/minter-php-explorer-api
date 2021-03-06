<?php

namespace App\Jobs;

use App\Http\Resources\BlockResource;
use App\Models\Block;

class BroadcastBlockJob extends Job
{
    public $queue = 'broadcast';

    /** @var Block */
    protected $block;

    /** @var \phpcent\Client */
    protected $centrifuge;

    /**
     * Create a new job instance.
     *
     * @param Block $block
     */
    public function __construct(Block $block)
    {
        $this->block = $block;
        $this->centrifuge = app(\phpcent\Client::class);
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $channel = env('MINTER_NETWORK', false) ? env('MINTER_NETWORK', 'mainnet') . '_blocks' : 'blocks';

        $this->centrifuge->publish($channel, new BlockResource($this->block));
    }
}
