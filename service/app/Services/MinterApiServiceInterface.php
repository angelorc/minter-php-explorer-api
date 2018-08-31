<?php

namespace App\Services;


interface MinterApiServiceInterface
{
    /**
     * Get node status data
     * @return array
     */
    public function getNodeStatusData(): array;

    /**
     * Get last block height from Minter Node API
     * @return int
     */
    public function getLastBlock(): int;

    /**
     * Get block data
     * @param int $blockHeight
     * @return array
     */
    public function getBlockData(int $blockHeight): array;

    /**
     * Get block's validators data
     * @param int $blockHeight
     * @return array
     */
    public function getBlockValidatorsData(int $blockHeight): array;

    /**
     * Get candidates data
     * @return array
     */
    public function getCandidatesData(): array;

    /**
     * Get address balance
     * @param string $address
     * @return array
     */
    public function getAddressBalance(string $address): array;

}