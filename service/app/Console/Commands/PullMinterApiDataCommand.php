<?php

namespace App\Console\Commands;

use App\Helpers\LogHelper;
use App\Services\BalanceServiceInterface;
use App\Services\BlockServiceInterface;
use App\Services\MinterService;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorServiceInterface;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

/**
 * Class PullMinterApiDataCommand
 * @package App\Console\Commands
 */
class PullMinterApiDataCommand extends Command
{
    use NodeTrait;

    /**
     * Time between API request, ms
     */
    protected const SLEEP_TIME = 2500000;

    protected const UPDATE_NODE_BLOCK_COUNT = 25;

    /** @var string */
    protected $signature = 'minter:api:pull-node-data';

    /** @var string */
    protected $description = 'Get data from minter node API';

    /** @var BlockServiceInterface */
    protected $blockService;

    /** @var TransactionServiceInterface */
    protected $transactionService;

    /** @var ValidatorServiceInterface */
    protected $validatorService;

    /** @var BalanceServiceInterface */
    protected $balanceService;

    /** @var MinterService */
    protected $minterService;

    /**
     * PullMinterApiDataCommand constructor.
     * @param BlockServiceInterface $blockService
     * @param TransactionServiceInterface $transactionService
     * @param BalanceServiceInterface $balanceService
     * @param ValidatorServiceInterface $validatorService
     */
    public function __construct(
        BlockServiceInterface $blockService,
        TransactionServiceInterface $transactionService,
        ValidatorServiceInterface $validatorService,
        BalanceServiceInterface $balanceService
    )
    {
        parent::__construct();
        $this->blockService = $blockService;
        $this->transactionService = $transactionService;
        $this->validatorService = $validatorService;
        $this->balanceService = $balanceService;
    }

    /**
     * Pull data from node
     */
    public function handle(): void
    {
        $this->updateNode();

        $blocksCount = 0;

        $this->info('Start pulling data from Minter Node API (' . $this->minterService->getNode()->fullLink . ')');

        try {

            $explorerCurrentBlockHeight = $this->blockService->getExplorerLastBlockHeight() + 1;
            $apiCurrentBlockHeight = $this->minterService->getLastBlock();

            $this->info('Begin from block: ' . $explorerCurrentBlockHeight);

            while (true) {

                $blocksDiff = $apiCurrentBlockHeight - $explorerCurrentBlockHeight;

                $start = microtime(1);

                if ($this->blockService->getExplorerLastBlockHeight() !== $explorerCurrentBlockHeight) {
                    $this->minterService->storeNodeData($explorerCurrentBlockHeight);
                }

                $end = microtime(1);
                $spentTime = $end - $start;

                if (env('APP_DEBUG', false)) {
                    $this->info('Block has been saved in ' . $spentTime . ' sec');
                }

                $apiCurrentBlockHeight = $this->minterService->getLastBlock();
                $explorerCurrentBlockHeight = $blocksDiff <= 2 ? $this->blockService->getExplorerLastBlockHeight() + 1 : $explorerCurrentBlockHeight + 1;
                if ($explorerCurrentBlockHeight > $apiCurrentBlockHeight) {
                    $explorerCurrentBlockHeight = $apiCurrentBlockHeight;
                }

                // If requests has been handled faster than 2.5 seconds
                if (2.5 > $spentTime && $blocksDiff <= 2) {
                    usleep($this::SLEEP_TIME - round($spentTime * 10 ** 6));
                }

                if ($blocksCount === $this::UPDATE_NODE_BLOCK_COUNT) {
                    $blocksCount = 0;
                    $this->updateNode();
                }

                $blocksCount++;

            }

        } catch (GuzzleException $exception) {
            //Try new node
            $this->updateNode();
            $this->warn('Minter Node URL has been changed to ' . $this->minterService->getNode()->fullLink);
            LogHelper::apiError($exception);
        } catch (\Exception $exception) {
            LogHelper::error($exception);
        }
    }

    private function updateNode(): void
    {
        $this->minterService = new MinterService(
            $this->getActualNode(),
            $this->blockService,
            $this->transactionService,
            $this->validatorService,
            $this->balanceService
        );
    }
}