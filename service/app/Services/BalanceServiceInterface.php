<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface BalanceServiceInterface
{
    /**
     * Get address balance
     * @param string $address
     * @return Collection
     */
    public function getAddressBalance(string $address): Collection;

    /**
     * @param string $address
     * @param array $balanceData
     * @return Collection
     */
    public function updateAddressBalanceFromAipData(string $address, array $balanceData): Collection;


    /**
     * Inform about balance change via WS
     * @param Collection $balances
     */
    public function broadcastNewBalances(Collection $balances): void;
}