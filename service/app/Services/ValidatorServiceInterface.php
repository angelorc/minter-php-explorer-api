<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface ValidatorServiceInterface
{

    /**
     * Save Validator to DB
     * @param int $blockHeight
     * @return Collection
     */
    public function saveValidatorsFromApiData(int $blockHeight): Collection;

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