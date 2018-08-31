<?php

namespace App\Repository;


use App\Helpers\StringHelper;
use App\Models\Balance;
use Illuminate\Database\Eloquent\Collection;

class BalanceRepository extends ModelRepository implements BalanceRepositoryInterface
{
    /**
     * BalanceRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Balance();
    }

    /**
     * Получить баланс по адресу
     * @param string $address
     * @return Collection
     */
    public function getBalanceByAddress(string $address): Collection
    {
        return $this->query()->where('address', 'ilike', $address)->get();
    }

    /**
     * Updete or create balance for address
     * @param string $address
     * @param string $coin
     * @param string $value
     * @return Balance
     */
    public function updateByAddressAndCoin(string $address, string $coin, string $value): Balance
    {
        return Balance::updateOrCreate(
            ['address' => StringHelper::mb_ucfirst($address), 'coin' => mb_strtoupper($coin)],
            ['amount' => $value]
        );
    }
}