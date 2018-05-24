<?php

namespace App\Services;


interface StatusServiceInterface
{

    /**
     * Получить высоту последнего блока
     * @return int
     */
    public function getLastBlockHeight(): int;

    /**
     * Получить количество транзакций в секунду
     * @return int
     */
    public function getTransactionsPerSecond(): float;

    /**
     * Получить среднее время обработки блока в секундах
     * @return int
     */
    public function getAverageBlockTime(): int;
}