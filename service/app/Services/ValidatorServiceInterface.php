<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface ValidatorServiceInterface
{

    /**
     * Store Validator to DB
     * @param array $validatorData
     * @return Collection
     */
    public function createFromAipData(array $validatorData): Collection;

    /**
     * Get Total Validators Count
     * @return int
     */
    public function getTotalValidatorsCount(): int;

    /**
     * Get Active Validators Count
     * @return int
     */
    public function getActiveValidatorsCount(): int;

    /**
     * Get Validator Stake
     * @param string $pk
     * @return array
     */
    public function getStake(string $pk): array;

    /**
     * @return string
     */
    public function getTotalStake(): string;

    /**
     * @param string $pk
     * @return int
     */
    public function getStatus(string $pk): int;

    /**
     * @param string $pk
     * @return array
     */
    public function getDelegatorList(string $pk): Collection;
}