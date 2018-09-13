<?php

namespace App\Services;

use App\Jobs\BroadcastBlockJob;
use App\Jobs\BroadcastStatusInfoJob;
use App\Jobs\SaveValidatorsJob;
use App\Jobs\StoreBlockEventsJob;
use App\Models\MinterNode;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Queue;

/**
 * Class MinterService
 * @package App\Services
 */
class MinterService extends MinterApiService implements MinterServiceInterface
{
    /** @var BlockServiceInterface */
    protected $blockService;

    /** @var TransactionServiceInterface */
    protected $transactionService;

    /** @var ValidatorServiceInterface */
    protected $validatorService;

    /** @var BalanceServiceInterface */
    protected $balanceService;

    /**
     * MinterApiService constructor.
     * @param MinterNode $node
     * @param BlockServiceInterface $blockService
     * @param TransactionServiceInterface $transactionService
     * @param ValidatorServiceInterface $validatorService
     * @param BalanceServiceInterface $balanceService
     */
    public function __construct(
        MinterNode $node,
        BlockServiceInterface $blockService,
        TransactionServiceInterface $transactionService,
        ValidatorServiceInterface $validatorService,
        BalanceServiceInterface $balanceService
    )
    {
        parent::__construct($node);
        $this->blockService = $blockService;
        $this->transactionService = $transactionService;
        $this->validatorService = $validatorService;
        $this->balanceService = $balanceService;
    }

    /**
     * Store date from node to DB
     * @param int $blockHeight
     * @throws GuzzleException
     */
    public function storeNodeData(int $blockHeight): void
    {
        $blockData = $this->getBlockData($blockHeight);
        $block = $this->blockService->createFromAipData($blockData);

        Queue::pushOn('broadcast', new BroadcastBlockJob($block));
        Queue::pushOn('broadcast', new BroadcastStatusInfoJob());
        Queue::pushOn('validators', new SaveValidatorsJob($block));

        if ($block->tx_count) {
            $this->transactionService->createFromAipDataAsync($blockData);
        }

        if (isset($blockData['events'])) {
            Queue::pushOn('block-events', new StoreBlockEventsJob($blockData));
        }
    }
}