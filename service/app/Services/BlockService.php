<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Block;
use App\Repository\BlockRepositoryInterface;
use Illuminate\Support\Carbon;

class BlockService implements BlockServiceInterface
{

    /** @var Client */
    private $client;

    /** @var BlockRepositoryInterface  */
    protected $blockRepository;

    /** @var TransactionServiceInterface  */
    protected $transactionService;

    /**
     * BlockService constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param TransactionServiceInterface $transactionService
     */
    public function __construct(BlockRepositoryInterface $blockRepository, TransactionServiceInterface $transactionService)
    {
        $this->client = new Client(['base_uri' => env('MINTER_API')]);

        $this->blockRepository = $blockRepository;

        $this->transactionService = $transactionService;
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
        $block->height     = $blockData['block']['header']['height'];
        $block->timestamp  = $blockTime->format('Y-m-d H:i:sO');
        $block->tx_count   = $blockData['block']['header']['num_txs'];
        $block->hash       = $blockData['block_meta']['block_id']['hash'];
        $block->block_reward = $this->getBlockReward($block->height);

        $transactions  = null;

        if ($block->tx_count > 0){
            $transactions =  $this->transactionService->decodeTransactionsFromApiData($blockData);
            $block->size = $this->getBlockSize($blockData);
        } else {
            $block->size = 0;
        }

        $this->blockRepository->save($block, $transactions);

    }

    /**
     * Получить размер блока
     * @param array $blockData
     * @return int
     */
    private function getBlockSize(array $blockData): int
    {
        $txs = '';

        foreach ($blockData['block']['data']['txs'] as $transaction){
            $txs .= $transaction;
        }

        return \mb_strlen($txs);
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
     * @param int $currentBlockHeight
     * @param string $timestamp
     * @return int
     */
    private function calculateBlockTime(int $currentBlockHeight, string $timestamp)
    {
        //TODO: добавить реализацию
        return 5;
    }

    /**
     * @param string $stringSateTime
     * @return \Carbon\Carbon
     */
    private function prepareDate(string $stringSateTime): \Carbon\Carbon
    {
        $nano = preg_replace('/(.*)\.(.*)Z/', '$2', $stringSateTime);

        if (!$nano){
            return \Carbon\Carbon::now();
        }

        $result = str_replace(".{$nano}Z", '.' . substr($nano, 0, 6) . 'Z', $stringSateTime);

        return new \Carbon\Carbon($result);
    }
}