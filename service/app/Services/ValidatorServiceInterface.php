<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface ValidatorServiceInterface
{

    /**
     * Save Validator to DB
     * @param array $data
     * @return Collection
     */
    public function saveValidatorsFromApiData(array $data): Collection;

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