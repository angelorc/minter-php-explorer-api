<?php

namespace App\Console\Commands;

use App\Services\BlockServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PullBlockDataCommand extends Command
{
    protected const SLEEP_TIME = 2500000;
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

    public function handle(): void
    {
        $this->info('Start pulling blocks data');

        try {

            $lastBlockHeight = $this->blockService->getLatestBlockHeight();
            $explorerLastBlockHeight = $this->blockService->getExplorerLatestBlockHeight() + 1;

            while (true) {

                $spentTime = 0;

                if ($lastBlockHeight >= $explorerLastBlockHeight) {
                    $start = time();
                    $blockData = $this->blockService->pullBlockData($explorerLastBlockHeight);
                    $this->blockService->saveFromApiData($blockData);
                    $explorerLastBlockHeight++;
                    $spentTime = time() - $start;
                }

                //Если блок обрабатывался меньше 2,5 сек, засыпаем
                $sleepTime = $this::SLEEP_TIME - 1000000 * $spentTime;
                if ($sleepTime > 0) {
                    usleep($spentTime);
                }

                $lastBlockHeight = $this->blockService->getLatestBlockHeight();
            }
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }
    }
}