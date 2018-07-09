<?php

namespace App\Services;


use App\Helpers\StringHelper;
use App\Models\Block;
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

        $block = Block::with('validators')->orderByDesc('height')->first();

        if ($block) {
            return $block->validators->count();
        }

        return 0;
    }

    /**
     * Get Total Validators Count
     * @return int
     */
    public function getTotalValidatorsCount(): int
    {

        $dt = new \DateTime();
        $dt->modify('-1 day');

        return Validator::whereDate('updated_at', '>=', $dt->format('Y-m-d H:i:sO'))->count();
    }

    /**
     * Save Validators to DB
     * @param int $blockHeight
     * @return Collection
     */
    public function saveValidatorsFromApiData(int $blockHeight): Collection
    {
        $validators = [];

        $validatorsData = null;

        $dateTime = new \DateTime();

        try {
            $data = $this->httpClient->request('GET', '/api/validators', [
                'query' => ['height' => $blockHeight]
            ]);

            $validatorsData = \GuzzleHttp\json_decode($data->getBody()->getContents(), true);

            $validatorsData = $validatorsData['result'];

        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }

        if ($validatorsData) {

            foreach ($validatorsData as $validatorData) {

                $validator = null;

                $validatorAddress = StringHelper::mb_ucfirst($validatorData['candidate_address'] ?? '');
                $validatorPubKey = StringHelper::mb_ucfirst($validatorData['pub_key'] ?? '');

                if ($validatorPubKey) {
                    $validator = Validator::updateOrCreate(
                        ['public_key' => $validatorPubKey, 'address' => $validatorAddress],
                        ['updated_at' => $dateTime->format('Y-m-d H:i:sO')]
                    );
                    $validators[] = $validator;
                }
            }
        }

        return collect($validators);
    }
}