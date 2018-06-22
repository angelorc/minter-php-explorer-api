<?php

namespace App\Services;


use App\Models\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class ValidatorService implements ValidatorServiceInterface
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * ValidatorService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

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
     * @param int $blockHeigth
     * @return Collection
     */
    public function saveValidatorsFromApiData(int $blockHeigth): Collection
    {
        $validators = [];

        $validatorsData = null;

        try {
            $data = $this->httpClient->request('GET', '/api/validators', [
                'query' => ['height' => $blockHeigth]
            ]);

            $validatorsData = \GuzzleHttp\json_decode($data->getBody()->getContents(), true);

            $validatorsData = $validatorsData['result'];
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }

        if ($validatorsData) {

            foreach ($validatorsData as $validatorData) {

                $validator = null;

                $validatorAddress = $validatorData['candidate_address'] ?? '';
                $validatorPubKey = $validatorData['pub_key'] ?? '';

                if ($validatorAddress) {
                    $validator = Validator::where('address', 'ilike', $validatorAddress)->first();
                }

                if (!$validator && $validatorAddress) {
                    $validator = new Validator();
                    $validator->name = '';
                    $validator->address = $validatorAddress;
                    $validator->public_key = $validatorPubKey;
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