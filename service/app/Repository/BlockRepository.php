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
}