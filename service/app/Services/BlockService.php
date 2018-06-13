<?php

namespace App\Services;

use App\Models\Block;
use App\Repository\BlockRepositoryInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class BlockService implements BlockServiceInterface
{

    protected const DEFAULT_BLOCK_TIME = 5;

    /** @var BlockRepositoryInterface */
    protected $blockRepository;
    /** @var TransactionServiceInterface */
    protected $transactionService;
    /** @var Client */
    protected $client;
    /** @var ValidatorServiceInterface */
    protected $validatorService;

    /**
     * BlockService constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param TransactionServiceInterface $transactionService
     * @param ValidatorServiceInterface $validatorService
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        TransactionServiceInterface $transactionService,
        ValidatorServiceInterface $validatorService
    ) {
        $this->client = new Client(['base_uri' => 'http://' . env('MINTER_API')]);

        $this->blockRepository = $blockRepository;

        $this->transactionService = $transactionService;

        $this->validatorService = $validatorService;
    }

    /**
     * Получить высоту последнего блока из API
     * @return int
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLatestBlockHeight(): int
    {
        $res = $this->client->request('GET', 'api/status');

        $data = json_decode($res->getBody()->getContents(), 1);

        return $data['result']['latest_block_height'];
    }

    /**
     * Получить данные блока по высоте из API
     * @param int $blockHeight
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pullBlockData(int $blockHeight): array
    {
        $res = $this->client->request('GET', "api/block/{$blockHeight}");

        $data = json_decode($res->getBody()->getContents(), 1);

        return $data['result'];
    }

    /**
     * Сохранить блок в базу из данных полученных через API
     * @param array $blockData
     */
    public function saveFromApiData(array $blockData): void
    {
        $blockTime = $this->prepareDate($blockData['block']['header']['time']);

        $block = new Block();
        $block->height = $blockData['block']['header']['height'];
        $block->timestamp = $blockTime->format('Y-m-d H:i:sO');
        $block->tx_count = $blockData['block']['header']['num_txs'];
        $block->hash = $blockData['block_meta']['block_id']['hash'];
        $block->block_reward = $this->getBlockReward($block->height);
        $block->block_time = $this->calculateBlockTime($blockTime->getTimestamp());

        $transactions = null;
        $validators = null;

        if ($block->tx_count > 0) {
            $transactions = $this->transactionService->decodeTransactionsFromApiData($blockData);
            $block->size = $this->getBlockSize($blockData);
        } else {
            $block->size = 0;
        }

        $validators = $this->validatorService->saveValidatorsFromApiData($blockData);

        $this->blockRepository->save($block, $transactions, $validators);

        $expiresAt = new \DateTime();
        try {
            $expiresAt->add(new \DateInterval('PT4S'));
        } catch (\Exception $e) {
        }

        Cache::forget('last_block_time');
        Cache::forget('last_block_height');
        Cache::forget('last_active_validators');

        Cache::put('last_block_time', $blockTime->getTimestamp(), $expiresAt);
        Cache::put('last_block_height', $block->height, $expiresAt);
        Cache::put('last_active_validators', $validators->count(), $expiresAt);
    }

    /**
     * @param string $stringSateTime
     * @return \Carbon\Carbon
     */
    private function prepareDate(string $stringSateTime): \Carbon\Carbon
    {
        $nano = preg_replace('/(.*)\.(.*)Z/', '$2', $stringSateTime);

        if (!$nano) {
            return \Carbon\Carbon::now();
        }

        $result = str_replace(".{$nano}Z", '.' . substr($nano, 0, 6) . 'Z', $stringSateTime);

        return new \Carbon\Carbon($result);
    }

    /**
     * Поучить награду за блок
     * @param int $blockHeight
     * @return int
     */
    private function getBlockReward(int $blockHeight): int
    {
        //TODO: добавить реализацию
        return 1;
    }

    /**
     * Получить размер блока
     * @param array $blockData
     * @return int
     */
    private function getBlockSize(array $blockData): int
    {
        $txs = '';

        foreach ($blockData['block']['data']['txs'] as $transaction) {
            $txs .= $transaction;
        }

        return \mb_strlen($txs);
    }

    /**
     * Получить высоту последнего блока из Базы
     * @return int
     * @throws \RuntimeException
     */
    public function getExplorerLatestBlockHeight(): int
    {
        $block = Block::orderByDesc('id')->first();

        return $block->height ?? 0;
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
     * @param int $currentBlockTime
     * @return int
     */
    private function calculateBlockTime(int $currentBlockTime): int
    {
        $lastBlockTime = Cache::get('last_block_time', null);

        if ($lastBlockTime && $currentBlockTime > $lastBlockTime) {
            return $currentBlockTime - $lastBlockTime;
        }

        return $this::DEFAULT_BLOCK_TIME;
    }
}