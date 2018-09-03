<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\MinterNode;
use App\Models\Transaction;
use GuzzleHttp\Exception\GuzzleException;

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
        $transactions = $this->transactionService->createFromAipData($blockData);

        if ($transactions->count()) {

            $balances =  collect([]);

            $transactions->each(function ($transaction) use(&$balances){
                /** @var Transaction $transaction */
                $data = $this->getAddressBalance($transaction->from);
                $balances = $balances->merge(
                    $this->balanceService->updateAddressBalanceFromAipData($transaction->from, $data['balance']));

                if (isset($transaction->to) && $transaction->from !== $transaction->to) {
                    $data = $this->getAddressBalance($transaction->to);
                    $balances = $balances->merge(
                        $this->balanceService->updateAddressBalanceFromAipData($transaction->to, $data['balance']));
                }
            });

            $this->balanceService->broadcastNewBalances($balances);
        }

        $validatorsData = $this->getBlockValidatorsData($blockHeight);
        $validators = $this->validatorService->createFromAipData($validatorsData);
        $block->validators()->saveMany($validators);
    }
}