<?php

namespace App\Repository;


use App\Models\Block;
use Illuminate\Support\Collection;

class BlockRepository implements BlockRepositoryInterface
{
    /**
     * @param Block $block
     * @param Collection|null $transactions
     */
    public function save(Block $block, Collection $transactions = null): void
    {
        $block->save();

        $block->validators()->sync(1);

        if ($transactions){
            $block->transactions()->saveMany($transactions);
        }

    }

    /**
     * Найти блок по Id
     * @param int $id
     * @return Block|null
     */
    public function findById(int $id): ?Block
    {
        return Block::find($id);
    }

    /**
     * Найти блок по Id
     * @param int $height
     * @return Block|null
     */
    public function findByHeight(int $height): ?Block
    {
        return Block::with('validators')->where('height', $height)->first();
    }

    /**
     * Получить все блоки
     * @param array $filter
     * @return Collection
     */
    public function getAll(array $filter = []): Collection
    {

        $query = Block::with('validators');

        if ($filter['validator_id']) {

            $query->whereHas('validators', function ($query) use ($filter) {
                $query->where('validators.id', $filter['validator_id']);
            });

        }

        return $query->orderByDesc('height')->get();

    }

    /**
     * Получить количество блоков за период в секундах
     * @param int $periodSec
     * @param \DateTime|null $endDate
     * @return int
     * @throws \Exception
     */
    public function getBlocksCountByPeriod(int $periodSec, \DateTime $endDate = null): int
    {
        $dt = $endDate ?? new \DateTime();
        $di = new \DateInterval("PT{$periodSec}S");

        return Block::whereDate('created_at', '>=', $dt->sub($di)->format('Y-m-d'))->count();
    }

    /**
     * Получить среднее время обработки блока за период в секундах
     * @param \DateTime|null $startDate
     * @return float
     * @throws \Exception
     */
    public function getAverageBlockTime(\DateTime $startDate = null): float
    {
        $start = new \DateTime();
        $start->sub(new \DateInterval('PT24H'));

        return Block::where('timestamp', '>=', $start->format('Y-m-d h:i:s'))->avg('block_time') ?? 0;
    }
}