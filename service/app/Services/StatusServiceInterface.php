<?php

namespace App\Services;


/**
 * Interface StatusServiceInterface
 * @package App\Services
 */
interface StatusServiceInterface
{

    /**
     * Получить высоту последнего блока
     * @return int
     */
    public function getLastBlockHeight(): int;

    /**
     * Получить среднее время обработки блока в секундах
     * @return float
     */
    public function getAverageBlockTime(): float;

    /**
     * Получить статус
     * @return bool
     */
    public function isActiveStatus(): bool;

    /**
     * @return float
     */
    public function getUpTime(): float;

    /**
     * @param string $coin
     * @param string $currency
     * @return float
     */
    public function getGetCurrentFiatPrice(string $coin, string $currency): float;

    /**
     * Get market capitalization value
     * @return float
     */
    public function getMarketCap(): float;

    /**
     * @return array
     */
    public function getStatusInfo(): array;
}