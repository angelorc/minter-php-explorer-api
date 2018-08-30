<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface BalanceServiceInterface
{
    /**
     * Получить баланс адреса
     * @param string $address
     * @return Collection
     */
    public function getAddressBalance(string $address): Collection;

    /**
     * Обновить баланс адреса данными из ноды
     * @param string $address
     */
    public function updateAddressBalanceFromNodeAPI(string $address): void;

    /**
     * Оповестить об изменении баланса подписчиков
     * @param string $address
     */
    public function broadcastNewBalances(string $address): void;
}