<?php

namespace App\Repository;


use App\Helpers\StringHelper;
use App\Models\Balance;
use App\Models\BalanceChannel;
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
            ['address' => mb_strtolower($address), 'coin' => mb_strtoupper($coin)],
            ['amount' => $value]
        );
    }

    /**
     * Get channels for WS broadcast
     * @param string $address
     * @return Collection
     */
    public function getChannelsForBalanceAddress(string $address): Collection
    {
        return BalanceChannel::where('address', 'ilike', $address)->get();
    }

    /**
     * Delete channels older than 10 days
     */
    public function deleteOldChannels(): void
    {
        $dt = new \DateTime();
        $dt->modify('-10 days');
        BalanceChannel::whereDate('created_at', '<=', $dt->format('Y-m-d H:i:s'));
    }
}