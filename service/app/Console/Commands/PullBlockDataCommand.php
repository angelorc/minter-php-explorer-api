<?php

namespace App\Console\Commands;

use App\Helpers\RmqHelper;
use App\Services\BlockServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

class PullBlockDataCommand extends Command
{

    protected const SLEEP_TIME = 1000000;

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
     * @var RmqHelper
     */
    private $rmqHelper;

    /**
     * PullBlockDataCommand constructor.
     * @param BlockServiceInterface $blockService
     */
    public function __construct(BlockServiceInterface $blockService)
    {
        parent::__construct();

        $this->blockService = $blockService;

        $this->rmqHelper = new RmqHelper(config('rmq.explorer.general'));
    }

    public function handle(): void
    {
        try {

            $lastBlockHeight = $this->blockService->getLatestBlockHeight();
            $explorerLastBlockHeight = $this->blockService->getExplorerLatestBlockHeight() + 1;

            while (true) {
                if ($lastBlockHeight > $explorerLastBlockHeight) {
                    $blockData = $this->blockService->pullBlockData($explorerLastBlockHeight);
                    $this->blockService->saveFromApiData($blockData);
                    $explorerLastBlockHeight++;
                } else {
                    usleep($this::SLEEP_TIME);
                    $lastBlockHeight = $this->blockService->getLatestBlockHeight();
                }
            }

        } catch (GuzzleException $e) {
        } catch (AMQPProtocolChannelException $e) {
            Log::error($e->getMessage());
        }

    }
}