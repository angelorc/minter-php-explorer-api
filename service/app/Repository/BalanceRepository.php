<?php

namespace App\Repository;


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
}