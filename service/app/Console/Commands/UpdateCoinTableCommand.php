<?php

namespace App\Console\Commands;

use App\Models\MinterNode;
use App\Models\Transaction;
use App\Repository\TransactionRepository;
use App\Repository\TransactionRepositoryInterface;
use App\Services\BalanceServiceInterface;
use App\Services\BlockServiceInterface;
use App\Services\CoinServiceInterface;
use App\Services\MinterApiService;
use App\Services\MinterService;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorServiceInterface;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class UpdateCoinTableCommand
 * @package App\Console\Commands
 */
class UpdateCoinTableCommand extends Command
{
    protected $signature = 'minter:coins:update';

    /** @var string */
    protected $description = 'Update or create coins';

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    /** @var CoinServiceInterface */
    protected $coinService;

    /**
     * UpdateCoinTableCommand constructor.
     * @param TransactionRepositoryInterface $transactionRepository
     * @param CoinServiceInterface $coinService
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        CoinServiceInterface $coinService
    )
    {
        parent::__construct();
        $this->transactionRepository = $transactionRepository;
        $this->coinService = $coinService;
    }

    /**
     * Update or create coins
     */
    public function handle(): void
    {
        $start = microtime(1);

       $transactions = $this->transactionRepository
           ->getAllQuery(['type' => Transaction::TYPE_CREATE_COIN])
           ->orderBy('created_at')
           ->get();

        $transactions->each(function($transaction){
            $this->coinService->createCoinFromTransactions($transaction);
        });

        $spentTime = microtime(1) - $start;

        $this->info('Created/Updated ' . $transactions->count() . ' coins in ' . $spentTime  . ' sec');
    }
}