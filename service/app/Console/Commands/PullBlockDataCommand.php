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
                    $message = ['blockHeight' => $explorerLastBlockHeight];
                    $this->rmqHelper->publish(\GuzzleHttp\json_encode($message), BlocksQueueWorkerCommand::QUEUE_NAME);
                    $this->info($explorerLastBlockHeight);
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