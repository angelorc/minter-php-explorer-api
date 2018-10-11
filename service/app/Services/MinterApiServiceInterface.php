<?php

namespace App\Services;


use GuzzleHttp\Exception\GuzzleException;

interface MinterApiServiceInterface
{
    /**
     * Get node status data
     * @throws GuzzleException
     * @return array
     */
    public function getNodeStatusData(): array;

    /**
     * Get last block height from Minter Node API
     * @throws GuzzleException
     * @return int
     */
    public function getLastBlock(): int;

    /**
     * Get block data
     * @param int $blockHeight
     * @throws GuzzleException
     * @return array
     */
    public function getBlockData(int $blockHeight): array;

    /**
     * Get block's validators data
     * @param int $blockHeight
     * @return array
     * @throws GuzzleException
     */
    public function getBlockValidatorsData(int $blockHeight): array;

    /**
     * Get candidates data
     * @param int $blockHeight
     * @return array
     * @throws GuzzleException
     */
    public function getCandidatesData(int $blockHeight): array;

    /**
     * Get address balance
     * @param string $address
     * @return array
     * @throws GuzzleException
     */
    public function getAddressBalance(string $address): array;

    /**
     * Get amount in base coin
     * @param string $coin
     * @param string $value
     * @return mixed
     * @throws GuzzleException
     */
    public function getBaseCoinValue(string $coin, string $value);

    /**
     * Get transactions count for address
     * @param string $address
     * @return array
     */
    public function getTransactionsCountByAddress(string $address): array;

    /**
     * Push transaction data to blockchain
     * @param string $txHash
     * @return array
     */
    public function pushTransactionToBlockChain(string $txHash): array;

    /**
     * @param string $coinToSell
     * @param string $coinToBuy
     * @param string $valueToSell
     * @return array
     */
    public function estimateSellCoin(string $coinToSell, string $coinToBuy, string $valueToSell): array;

    /**
     * @param string $coinToSell
     * @param string $coinToBuy
     * @param string $valueToBuy
     * @return array
     */
    public function estimateBuyCoin(string $coinToSell, string $coinToBuy, string $valueToBuy): array;

    /**
     * @param string $tx
     * @return array
     */
    public function estimateTxCommission(string $tx): array;

    /**
     * @param string $coin
     * @return array
     */
    public function getCoinInfo(string $coin): array;

}