<?php

namespace App\Services;


use App\Helpers\LogHelper;
use App\Helpers\MathHelper;
use App\Models\Block;
use App\Repository\BlockRepositoryInterface;
use App\Traits\NodeTrait;
use Illuminate\Support\Facades\Cache;

class StatusService implements StatusServiceInterface
{
    use NodeTrait;

    /** Время от последнего блока при котором статус счетается активным в секундах */
    public const IS_ACTIVE_PERIOD = 15;

    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /** @var TransactionServiceInterface */
    protected $transactionService;

    /**  @var BlockServiceInterface */
    protected $blockService;

    /** @var CoinServiceInterface */
    protected $coinService;

    /**
     * StatusService constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param TransactionServiceInterface $transactionService
     * @param BlockServiceInterface $blockService
     * @param CoinServiceInterface $coinService
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        TransactionServiceInterface $transactionService,
        BlockServiceInterface $blockService,
        CoinServiceInterface $coinService
    )
    {
        $this->blockRepository = $blockRepository;
        $this->transactionService = $transactionService;
        $this->blockService = $blockService;
        $this->coinService = $coinService;
    }

    /**
     * Получить высоту последнего блока
     * @return int
     */
    public function getLastBlockHeight(): int
    {
        $height = Cache::get('latest_block_height');

        if (!$height) {
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

        if ($total) {
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
        //TODO: заменить расчетом
        return 0.01;
    }

    /**
     * Get market capitalization value
     * @return float
     */
    public function getMarketCap(): float
    {
        $apiService = new MinterApiService($this->getActualNode());

        $baseCoinUsdPrice = $this->getGetCurrentFiatPrice();

        $coins = $this->coinService->getTotalAmountByCoins();

        $result = $coins['MNT'] ?? 0;

        foreach ($coins as $coin => $amount) {
            if ($coin !== 'MNT') {
                $data = $apiService->getBaseCoinValue($coin, $amount);
                $result = bcadd($result, $data);
            }
        }

        return bcmul(MathHelper::makeAmountFromIntString($result), $baseCoinUsdPrice, 4);
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getStatusInfo(): array
    {
        $baseCoin = env('MINTER_BASE_COIN', 'MNT');
        $interval = 1;
        try {
            $interval = new \DateInterval('PT3S');
        } catch (\Exception $e) {
            LogHelper::error($e);
        }

        $bipPriceUsd = Cache::get('bipPriceUsd', null);
        if (!$bipPriceUsd) {
            $bipPriceUsd = $this->getGetCurrentFiatPrice($baseCoin, 'USD');
            Cache::put('bipPriceUsd', $bipPriceUsd, $interval);
        }

        $marketCap = Cache::get('marketCap', null);
        if (!$marketCap) {
            $marketCap = $this->getMarketCap();
            Cache::put('marketCap', $marketCap, $interval);
        }

        $latestBlockHeight = Cache::get('latestBlockHeight', null);
        $latestBlockTime = Cache::get('latestBlockTime', null);

        if (!$latestBlockHeight || !$latestBlockTime) {
            $block = $this->blockService->getExplorerLastBlock();

            if ($block) {
                $latestBlockHeight = $block->height;
                $latestBlockTime = $block->formattedDate;
                Cache::put('latestBlockHeight', $latestBlockHeight, $interval);
                Cache::put('latestBlockTime', $latestBlockTime, $interval);
            } else {
                $latestBlockHeight = 0;
                $latestBlockTime = 0;
            }
        }

        $totalTransactions = Cache::get('totalTransactions', null);
        if (!$totalTransactions) {
            $totalTransactions = $this->transactionService->getTotalTransactionsCount();
            Cache::put('totalTransactions', $totalTransactions, $interval);
        }

        $transactionsPerSecond = Cache::get('transactionsPerSecond', null);
        if (!$transactionsPerSecond) {
            $transactionsPerSecond = $this->transactionService->getTransactionsSpeed();
            Cache::put('transactionsPerSecond', $transactionsPerSecond, $interval);
        }

        $averageBlockTime = Cache::get('averageBlockTime', null);
        if (!$averageBlockTime) {
            $averageBlockTime = $this->getAverageBlockTime();
            Cache::put('averageBlockTime', $averageBlockTime, $interval);
        }

        //TODO: поменять значения, как станет ясно откуда брать
        return [
            'bipPriceUsd' => $bipPriceUsd,
            'bipPriceBtc' => 0.0000015883176063418346,
            'bipPriceChange' => 10,
            'marketCap' => $marketCap,
            'latestBlockHeight' => $latestBlockHeight,
            'latestBlockTime' => $latestBlockTime,
            'totalTransactions' => $totalTransactions,
            'transactionsPerSecond' => $transactionsPerSecond,
            'averageBlockTime' => $averageBlockTime,
        ];
    }
}