<?php

namespace App\Console\Commands;

use App\Models\Block;
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
        $lastBlockHeight = $this->blockService->getLatestBlockHeight();

        $explorerLastBlockHeight =$this->blockService->getExplorerLatestBlockHeight() + 1;

        while (true) {

            if ($lastBlockHeight > $explorerLastBlockHeight){
                $blockData = $this->blockService->pullBlockData($explorerLastBlockHeight);
                $this->blockService->saveFromApiData($blockData);
                $explorerLastBlockHeight++;
            }else{
                usleep(2500000);
                $lastBlockHeight = $this->blockService->getLatestBlockHeight();
            }

        }
    }
}