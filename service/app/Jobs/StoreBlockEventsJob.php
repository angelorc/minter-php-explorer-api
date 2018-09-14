<?php

namespace App\Jobs;

use App\Models\Reward;
use App\Models\Slash;

class StoreBlockEventsJob extends Job
{

    public $queue = 'block-events';

    /** @var array */
    protected $blockData;

    /**
     * Create a new job instance.
     *
     * @param array $blockData
     */
    public function __construct(array $blockData)
    {
        $this->blockData = $blockData;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->blockData['events'] as $event) {
            if ($event['type'] === 'minter/RewardEvent') {
                $reward = new Reward();
                $reward->block_height = $event['height '];
                $reward->address = $event['value']['address'];
                $reward->role = $event['value']['role'];
                $reward->amount = $event['value']['amount'];
                $reward->validator_pk = $event['value']['validator_pub_key'];
                $reward->save();
            }
            if ($event['type'] === 'minter/SlashEvent') {
                $slash = new Slash();
                $slash->block_height = $event['height '];
                $slash->address = $event['value']['address'];
                $slash->coin = $event['value']['coin'];
                $slash->amount = $event['value']['amount'];
                $slash->validator_pk = $event['value']['validator_pub_key'];
                $slash->save();
            }
        }
    }

}
