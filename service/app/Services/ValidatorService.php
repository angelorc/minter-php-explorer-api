<?php

namespace App\Services;


use App\Models\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ValidatorService implements ValidatorServiceInterface
{

    /**
     * Get Active Validators Count
     * @return int
     */
    public function getActiveValidatorsCount(): int
    {
        return Cache::get('last_active_validators', 4);
    }

    /**
     * Get Total Validators Count
     * @return int
     */
    public function getTotalValidatorsCount(): int
    {
        $total = Cache::get('last_total_validators', null);

        if (!$total) {

            $total = Validator::count();

            Cache::put('last_total_validators', $total, 10);
        }


        return $total;
    }

    /**
     * Save Validators to DB
     * @param array $data
     * @return Collection
     */
    public function saveValidatorsFromApiData(array $data): Collection
    {
        $validators = [];

        $validatorsData = $data['block']['last_commit']['precommits'];

        if ($validatorsData) {

            foreach ($validatorsData as $validatorData) {

                $validator = null;

                $validatorAddress = $validatorData['validator_address'] ?? '';

                if ($validatorAddress) {
                    $validator = Validator::where('address', $validatorAddress)->first();
                }

                if (!$validator && $validatorAddress) {
                    $validator = new Validator();
                    $validator->name = '';
                    $validator->address = mb_strtoupper($validatorData['validator_address']);
                    $validator->save();
                }

                if ($validator) {
                    $validators[] = $validator;
                }
            }
        }

        return collect($validators);
    }
}