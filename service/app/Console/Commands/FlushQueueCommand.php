<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

/**
 * Class UpdateCoinTableCommand
 * @package App\Console\Commands
 */
class FlushQueueCommand extends Command
{
    protected $signature = 'minter:queue:flush';

    /** @var string */
    protected $description = 'Flush queue';

    /**
     * UpdateCoinTableCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Update or create coins
     */
    public function handle(): void
    {
        Redis::connection()->del(['queues:broadcast']);
    }
}