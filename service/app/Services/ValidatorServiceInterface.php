<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface ValidatorServiceInterface
{

    /**
     * Save Validator to DB
     * @param int $blockHeigth
     * @return Collection
     */
    public function saveValidatorsFromApiData(int $blockHeigth): Collection;

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