<?php

namespace App\Services;


use App\Models\Block;
use App\Repository\BlockRepositoryInterface;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class StatusService implements StatusServiceInterface
{
    /**
     * Время от последнего блока при котором статус счетается активным в секундах
     */
    public const IS_ACTIVE_PERIOD = 15;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var BlockServiceInterface
     */
    protected $blockService;

    /**
     * StatusService constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BlockServiceInterface $blockService
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        TransactionRepositoryInterface $transactionRepository,
        BlockServiceInterface $blockService
    ) {
        $this->blockRepository = $blockRepository;
        $this->transactionRepository = $transactionRepository;
        $this->blockService = $blockService;
    }

    /**
     * Получить высоту последнего блока
     * @return int
     */
    public function getLastBlockHeight(): int
    {
        $height = Cache::get('latest_block_height');

        if (!$height){
            return Block::orderBy('created_at', 'desc')->first()->height ?? 0;
        }

        return $height;
    }

    /**
     * Получить среднее время обработки блока в секундах
     * @return float
     * @throws \Exception
     */
    public function getAverageBlockTime(): float
    {
        return $this->blockRepository->getAverageBlockTime();
    }

    /**
     * Получить статус
     * @return bool
     */
    public function isActiveStatus(): bool
    {
        /** @var Block $lastBlock */
        $lastBlock = Block::orderByDesc('height')->first();

        if ($lastBlock) {
            $lastBlockTime = new \DateTime($lastBlock->created_at);
            return time() - $lastBlockTime->getTimestamp() <= $this::IS_ACTIVE_PERIOD;
        }

        return false;
    }

    /**
     * @return float
     */
    public function getUpTime(): float
    {
        $dt = new \DateTime();
        $dt->modify('-1 day');

        $total = Block::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))->count();
        $slow = Block::whereDate('created_at', '>=', $dt->format('Y-m-d H:i:s'))
            ->where('block_time', '>=', 6)->count();

        if($total){
            return 1 - $slow / $total;
        }

        return false;

    }

    /**
     * @param string $coin
     * @param string $currency
     * @return float
     */
    public function getGetCurrentFiatPrice(string $coin = 'MNT', string $currency = 'USD'): float
    {
        return 0.01;
    }
}