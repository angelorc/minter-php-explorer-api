<?php

namespace App\Services;

use App\Helpers\StringHelper;
use App\Models\MinterNode;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class MinterApiService
 * @package App\Services
 */
class MinterApiService implements MinterApiServiceInterface
{
    /** @var HttpClient */
    protected $httpClient;

    /** @var MinterNode */
    protected $node;

    /**
     * MinterApiService constructor.
     * @param MinterNode $node
     */
    public function __construct(MinterNode $node)
    {
        $this->node = $node;
        $this->httpClient = new HttpClient([
            'base_uri' => $this->node->fullLink,
            'Connection' => 'close',
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
    }

    /**
     * @return MinterNode
     */
    public function getNode(): MinterNode
    {
        return $this->node;
    }

    /**
     * Get node status data
     * @return array
     * @throws GuzzleException
     */
    public function getNodeStatusData(): array
    {
        $res = $this->httpClient->request('GET', 'api/status', ['timeout' => 10]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Get last block from Minter Node API
     * @return int
     * @throws GuzzleException
     */
    public function getLastBlock(): int
    {
        $result = $this->getNodeStatusData();
        return $result['latest_block_height'];
    }

    /**
     * Get block data
     * @param int $blockHeight
     * @return array
     * @throws GuzzleException
     */
    public function getBlockData(int $blockHeight): array
    {
        $res = $this->httpClient->request('GET', 'api/block/' . $blockHeight, [
            'timeout' => 30,
            'query' => ['withEvents' => true]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Get block validators data
     * @param int $blockHeight
     * @return array
     * @throws GuzzleException
     */
    public function getBlockValidatorsData(int $blockHeight): array
    {
        $res = $this->httpClient->request('GET', 'api/validators/', [
            'timeout' => 11,
            'query' => ['height' => $blockHeight]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Get block validators data
     * @param int $blockHeight
     * @return array
     * @throws GuzzleException
     */
    public function getCandidatesData(int $blockHeight): array
    {
        $res = $this->httpClient->request('GET', 'api/candidates/', [
            'timeout' => 12,
            'query' => ['height' => $blockHeight]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Get address balance
     * @param string $address
     * @return array
     * @throws GuzzleException
     */
    public function getAddressBalance(string $address): array
    {
        $res = $this->httpClient->request('GET', 'api/balance/' . StringHelper::mb_ucfirst($address), ['timeout' => 15]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Get amount in base coin
     * @param string $coin
     * @param string $value
     * @return string
     */
    public function getBaseCoinValue(string $coin, string $value): string
    {
        $baseCoin = env('MINTER_BASE_COIN', 'MNT');
        try {
            $res = $this->httpClient->request('GET', 'api/estimateCoinSell',
                [
                    'timeout' => 13,
                    'query' => [
                    'coin_to_sell' => $coin,
                    'value_to_sell' => $value,
                    'coin_to_buy' => $baseCoin
                ]]);
            $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
            return $data['result']['will_get'];
        } catch (GuzzleException $e) {
            return 0;
        }

    }

    /**
     * Get transactions count for address
     * @param string $address
     * @return array
     * @throws GuzzleException
     */
    public function getTransactionsCountByAddress(string $address): array
    {
        $res = $this->httpClient->request('GET', 'api/transactionCount/' . StringHelper::mb_ucfirst($address));
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * Push transaction data to blockchain
     * @param string $txHash
     * @return array
     * @throws GuzzleException
     */
    public function pushTransactionToBlockChain(string $txHash): array
    {
        $res = $this->httpClient->request('POST', 'https://gate.minter.network/api/v1/transaction/push', [
            'timeout' => 20,
            'json' => ['transaction' => $txHash]
        ]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data;
    }

    /**
     * @param string $coinToSell
     * @param string $coinToBuy
     * @param string $valueToSell
     * @return array
     * @throws GuzzleException
     */
    public function estimateSellCoin(string $coinToSell, string $coinToBuy, string $valueToSell): array
    {
        $res = $this->httpClient->request('GET', 'api/estimateCoinSell',
            [
                'timeout' => 14,
                'query' => [
                'coin_to_sell' => $coinToSell,
                'value_to_sell' => $valueToSell,
                'coin_to_buy' => $coinToBuy
            ]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * @param string $coinToSell
     * @param string $coinToBuy
     * @param string $valueToBuy
     * @return array
     * @throws GuzzleException
     */
    public function estimateBuyCoin(string $coinToSell, string $coinToBuy, string $valueToBuy): array
    {
        $res = $this->httpClient->request('GET', 'api/estimateCoinBuy',
            [
                'timeout' => 16,
                'query' => [
                'coin_to_sell' => $coinToSell,
                'value_to_buy' => $valueToBuy,
                'coin_to_buy' => $coinToBuy
            ]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * @param string $tx
     * @return array
     * @throws GuzzleException
     */
    public function estimateTxCommission(string $tx): array
    {
        $res = $this->httpClient->request('GET', 'api/estimateTxCommission',
            [
                'timeout' => 17,
                'query' => [
                'tx' => $tx,
            ]]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }

    /**
     * @param string $coin
     * @return array
     * @throws GuzzleException
     */
    public function getCoinInfo(string $coin): array
    {
        $res = $this->httpClient->request('GET', 'api/coinInfo/' . mb_strtoupper($coin), ['timeout' => 18]);
        $data = \GuzzleHttp\json_decode($res->getBody()->getContents(), 1);
        return $data['result'];
    }
}