<?php

namespace App\Services;


use App\Models\Coin;
use App\Repository\BalanceRepositoryInterface;
use Illuminate\Support\Collection;

class BalanceService implements BalanceServiceInterface
{
    /**
     * @var BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * BalanceService constructor.
     * @param BalanceRepositoryInterface $balanceRepository
     */
    public function __construct(BalanceRepositoryInterface $balanceRepository)
    {
        $this->balanceRepository = $balanceRepository;
    }

    /**
     * Получить баланс адреса
     * @param string $address
     * @return Collection
     */
    public function getAddressBalance(string $address): Collection
    {
        $result = $this->balanceRepository->getBalanceByAddress($address)->map(function($item){
            return new Coin($item->coin, $item->amount);
        });

        return $result;
    }
}