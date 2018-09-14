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
        $this->centrifuge->publish('blocks', new BlockResource($this->block));
    }
}
