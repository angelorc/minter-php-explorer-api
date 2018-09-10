<?php

namespace App\Services;


use App\Models\Block;

interface BlockServiceInterface
{
    /**
     * Store block data to DB
     * @param array $blockData
     * @return Block
     */
    public function createFromAipData(array $blockData): Block;

    /**
     * Скорость обработки блоков за последние 24 часа
     * @return float
     */
    public function blockSpeed24h(): float;

    /**
     * Получить высоту последнего блока из Базы
     * @return int
     */
    public function getExplorerLastBlockHeight(): int;

    /**
     * Get last block form DB
     * @return Block
     */
    public function getExplorerLastBlock(): Block;

}