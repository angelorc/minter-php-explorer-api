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
}