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
        if($blockHeight <= 1 ){
            return collect([]);
        }

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
                $candidate = $validatorData['candidate'];
                $validatorAddress = StringHelper::mb_ucfirst($candidate['candidate_address'] ?? '');
                $validatorPubKey = StringHelper::mb_ucfirst($candidate['pub_key'] ?? '');

                $data = [
                    'pub_key' => $validatorPubKey,
                    'candidate_address' => $validatorAddress,
                    'accumulated_reward' => $validatorData['accumulated_reward'],
                    'absent_times' => $validatorData['absent_times'],
                    'total_stake' => $candidate['total_stake'],
                    'commission' => $candidate['commission'],
                    'status' => $candidate['status'],
                    'created_at_block' => $candidate['created_at_block'],
                    'updated_at' => $dateTime->format('Y-m-d H:i:sO')
                ];

                if ($validatorPubKey) {
                    $validator = Validator::updateOrCreate(
                        ['pub_key' => $validatorPubKey, 'candidate_address' => $validatorAddress],
                        $data
                    );

                    $validators[] = $validator;
                }
            }
        }

        return collect($validators);
    }
}