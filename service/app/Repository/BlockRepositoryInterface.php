<?php

namespace App\Repository;


use App\Models\Block;
use Illuminate\Support\Collection;

interface BlockRepositoryInterface
{
    /**
     * Сохранить блок
     * @param Block $block
     * @param Collection $transactions
     */
    public function save(Block $block, Collection $transactions = null): void;

    /**
     * Найти блок по Id
     * @param int $id
     * @return Block|null
     */
    public function findById(int $id): ?Block;

    /**
     * Найти блок по Id
     * @param int $height
     * @return Block|null
     */
    public function findByHeight(int $height): ?Block;

    /**
     * Получить все блоки
     * @param array $filter
     * @return Collection
     */
    public function getAll(array $filter = []): Collection;

    /**
     * Получить количество блоков за период в секундах
     * @param int $periodSec
     * @param \DateTime|null $endDate
     * @return int
     */
    public function getBlocksCountByPeriod(int $periodSec, \DateTime $endDate = null): int;

    /**
     * Получить среднее время обработки блока за период в секундах
     * @param \DateTime|null $startDate
     * @return float
     */
    public function getAverageBlockTime(\DateTime $startDate = null): float;

}