<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\Transaction;
use App\Repository\TransactionRepositoryInterface;
use App\Services\CoinServiceInterface;
use Illuminate\Console\Command;

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

        Coin::updateOrCreate(['symbol' => 'MNT'],
            [
                'name' => 'Minter Coin',
                'volume' => 0,
                'reserve_balance' => 0,
                'crr' => 0,
                'creator' => '',
                'created_at' => '2018-01-01',
            ]);

        $transactions->each(function ($transaction) {
            $this->coinService->createCoinFromTransactions($transaction);
        });

        $spentTime = microtime(1) - $start;

        $this->info('Created/Updated ' . $transactions->count() . ' coins in ' . $spentTime . ' sec');
    }
}