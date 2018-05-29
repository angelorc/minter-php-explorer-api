<?php

namespace App\Services;


use App\Models\Block;
use Carbon\Carbon;
use App\Repository\BlockRepositoryInterface;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class StatusService implements StatusServiceInterface
{
    /**
     * Время от последнего блока при котором статус счетается активным
     * в секундах
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
     * StatusService constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->blockRepository = $blockRepository;
        $this->transactionRepository = $transactionRepository;
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
     * @return int
     */
    public function getAverageBlockTime(): int
    {
        //TODO: добавить реализацию
        return 5;
    }

    /**
     * Получить статус
     * @return bool
     */
    public function isActiveStatus(): bool
    {
        $lastBlockTime = Cache::get('last_block_time', 0);

        return time() - $lastBlockTime <= $this::IS_ACTIVE_PERIOD;
    }

    /**
     * @return int
     */
    public function getUpTime(): int
    {
        //Период 30 дней в секундах
        $period = 30 * 24 * 3600;

        //Теоретическое кол-во блоков в месяц
        $theoryBlocks = 60 / 5 * 60 * 24 * 30;

        //Реальное кол-во блоков за месяц
        $count = $this->blockRepository->getBlocksCountByPeriod($period);

        if ($count === 0) {
            return 0;
        }

        return round($theoryBlocks / $count / 30);
    }

    /**
     * @param int $periodInSeconds
     * @return int
     */
    public function getSpeedOfBlocks(int $periodInSeconds = 86400): int
    {
        // TODO: Implement getSpeedOfBlocks() method.
    }
}