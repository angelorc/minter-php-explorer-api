<?php

namespace App\Services;


use App\Models\Coin;
use App\Models\Transaction;
use App\Repository\CoinsRepositoryInterface;

class CoinService implements CoinServiceInterface
{
    /** @var CoinsRepositoryInterface */
    protected $coinsRepository;

    /**
     * CoinService constructor.
     * @param CoinsRepositoryInterface $coinsRepository
     */
    public function __construct(CoinsRepositoryInterface $coinsRepository)
    {
        $this->coinsRepository = $coinsRepository;
    }

    /**
     * Create coin from transaction
     * @param Transaction $transaction
     * @return Coin
     */
    public function createCoinFromTransactions(Transaction $transaction): ?Coin
    {
        if ($transaction->type === Transaction::TYPE_CREATE_COIN){
            return $this->coinsRepository->createCoinFromTransactions($transaction);
        }

        return null;
    }

    /**
     * Total coins in network by coin
     * @return array
     */
    public function getTotalAmountByCoins(): array
    {
        return $this->coinsRepository->getTotalAmountByCoins();
    }
}