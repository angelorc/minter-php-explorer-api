<?php

namespace App\Services;


interface BlockServiceInterface
{

    /**
     * Получить высоту последнего блока из API
     * @return int
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLatestBlockHeight(): int;

    /**
     * Получить высоту последнего блока из Базы
     * @return int
     */
    public function getExplorerLatestBlockHeight(): int;

    /**
     * Получить данные блока по высоте из API
     * @param int $blockHeight
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pullBlockData(int $blockHeight): array;

    /**
     * Сохранить блок в базу из данных полученных через API
     * @param array $blockData
     */
    public function saveFromApiData(array $blockData): void;

    /**
     * Скорость обработки блоков за последние 24 часа
     * @return float
     */
    public function blockSpeed24h(): float;
}