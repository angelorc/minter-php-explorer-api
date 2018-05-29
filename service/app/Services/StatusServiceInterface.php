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
     * @return int
     */
    public function getAverageBlockTime(): int;

    /**
     * Получить статус
     * @return bool
     */
    public function isActiveStatus(): bool;

    /**
     * @return int
     */
    public function getUpTime(): int;

    /**
     * @param int $periodInSeconds
     * @return int
     */
    public function getSpeedOfBlocks(int $periodInSeconds = 86400): int;
}