<?php

namespace App\Services;

use App\Helpers\DateTimeHelper;
use App\Models\Block;
use App\Repository\BlockRepositoryInterface;

class BlockService implements BlockServiceInterface
{
    protected const DEFAULT_BLOCK_TIME = 5;

    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /**
     * BlockService constructor.
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(BlockRepositoryInterface $blockRepository)
    {
        $this->blockRepository = $blockRepository;
    }

    /**
     * СStore block data to DB
     * @param array $blockData
     * @return Block
     */
    public function createFromAipData(array $blockData): Block
    {
        $blockTime = DateTimeHelper::parse($blockData['time']);

        $block = new Block();
        $block->height = $blockData['height'];
        $block->created_at = $blockTime->format('Y-m-d H:i:sO');
        $block->tx_count = $blockData['num_txs'];
        $block->hash = 'Mh' . mb_strtolower($blockData['hash']);
        $block->block_reward = $blockData['block_reward'];
        $block->size = 0; //TODO: получать из API
        $block->timestamp = DateTimeHelper::getDateTimeAsFloat($blockData['time']);
        $block->block_time = $this->calculateBlockTime($block->timestamp);

        return $this->blockRepository->save($block);
    }

    /**
     * Get max block height
     * @return int
     */
    public function getExplorerLastBlockHeight(): int
    {
        return $this->blockRepository->getLastBlock()->height ?? 0;
    }

    /**
     * Скорость обработки блоков за последние 24 часа
     * @return float
     */
    public function blockSpeed24h(): float
    {
        $blocks = $this->blockRepository->getBlocksCountByPeriod(86400);

        return round($blocks / 86400, 8);
    }

    /**
     * @param string $currentBlockTime
     * @return float
     */
    private function calculateBlockTime(string $currentBlockTime): float
    {
        $lastBlock = $this->blockRepository->getLastBlock();

        if (!$lastBlock) {
            return $this::DEFAULT_BLOCK_TIME;
        }

        return (float)$currentBlockTime - (float)$lastBlock->timestamp;
    }

}