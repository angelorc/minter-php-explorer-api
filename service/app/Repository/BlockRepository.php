<?php

namespace App\Repository;


use App\Models\Block;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlockRepository implements BlockRepositoryInterface
{
    /**
     * @param Block $block
     * @return Block
     */
    public function save(Block $block): Block
    {
        $block->save();
        return $block;

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
        $dt = $startDate ?? new \DateTime();
        $dt->modify('-1 day');

        $result = DB::select('
            select  avg(block_time)  as avg
            from blocks
            where created_at >= :date ;
        ', ['date' => $dt->format('Y-m-d H:i:s')]);

        return  $result[0]->avg ?? 0;
    }


    /**
     * Get last block by height
     * @return mixed
     */
    public function getLastBlock(): ?Block
    {
        return Block::orderByDesc('height')->first();
    }
}