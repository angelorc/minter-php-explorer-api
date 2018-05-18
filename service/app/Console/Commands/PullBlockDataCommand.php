<?php

namespace App\Console\Commands;

use App\Services\BlockServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PullBlockDataCommand extends Command
{

    /**
     * @var string
     */
    protected $signature = 'block:pull';

    /**
     * @var string
     */
    protected $description = 'Pull last block data';

    /** @var BlockServiceInterface */
    private $blockService;

    /**
     * PullBlockDataCommand constructor.
     * @param BlockServiceInterface $blockService
     */
    public function __construct(BlockServiceInterface $blockService)
    {
        parent::__construct();

        $this->blockService = $blockService;
    }

    /**
     *
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        while (true) {

            $blockHeight = $this->blockService->getLatestBlockHeight();

            if ($blockHeight !== (int)Cache::get('latest_block_height')) {

                Cache::forget('latest_block_height');

                $blockData = $this->blockService->pullBlockData($blockHeight);

                $this->blockService->saveFromApiData($blockData);

                Cache::put('latest_block_height', $blockHeight, 1);
            }

            usleep(2500000);

        }
    }
}