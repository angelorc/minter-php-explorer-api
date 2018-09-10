<?php

namespace App\Console\Commands;

use App\Services\BalanceServiceInterface;
use App\Services\BlockServiceInterface;
use App\Services\MinterService;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorServiceInterface;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $minterService = new MinterService($this->getActualNode(), $this->blockService, $this->transactionService, $this->validatorService, $this->balanceService);

        $this->info('Start pulling data from Minter Node API (' . $minterService->getNode()->fullLink . ')');

        try {

            $explorerCurrentBlockHeight = $this->blockService->getExplorerLastBlockHeight() + 1;
            $apiCurrentBlockHeight = $minterService->getLastBlock();

            $this->info('Begin from block: ' . $explorerCurrentBlockHeight);

            while (true) {

                $blocksDiff = $apiCurrentBlockHeight - $explorerCurrentBlockHeight;

                $start = microtime(1);

                if ($this->blockService->getExplorerLastBlockHeight() !== $explorerCurrentBlockHeight) {
                    $minterService->storeNodeData($explorerCurrentBlockHeight);
                }

                $end = microtime(1);
                $spentTime = $end - $start;

                if (env('APP_DEBUG', false)) {
                    $this->info('Block has been saved in ' . $spentTime . ' sec');
                }

                $apiCurrentBlockHeight = $minterService->getLastBlock();
                $explorerCurrentBlockHeight = $blocksDiff <= 2 ? $this->blockService->getExplorerLastBlockHeight() + 1 : $explorerCurrentBlockHeight + 1;
                if ($explorerCurrentBlockHeight > $apiCurrentBlockHeight) {
                    $explorerCurrentBlockHeight = $apiCurrentBlockHeight;
                }

                // If requests has been handled faster than 2.5 seconds
                if (2.5 > $spentTime && $blocksDiff <= 2) {
                    usleep($this::SLEEP_TIME - round($spentTime * 10 ** 6));
                }
            }

        } catch (GuzzleException $exception) {
            //Try new node
            $minterService = new MinterService($this->getActualNode(), $this->blockService, $this->transactionService, $this->validatorService, $this->balanceService);

            $this->warn('Minter Node URL has been changed to ' . $minterService->getNode()->fullLink);

            Log::channel('api')->error(
                $exception->getFile() . ' line ' .
                $exception->getLine() . ': ' .
                $exception->getMessage()
            );
        } catch (\Exception $exception) {
            Log::error(
                $exception->getFile() . ' line ' .
                $exception->getLine() . ': ' .
                $exception->getMessage()
            );
        }

    }
}