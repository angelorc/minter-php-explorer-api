<?php

namespace App\Services;


use App\Helpers\StringHelper;
use App\Models\Block;
use App\Models\Validator;
use App\Repository\ValidatorRepositoryInterface;
use Illuminate\Support\Collection;


class ValidatorService implements ValidatorServiceInterface
{
    protected $validatorRepository;

    /**
     * ValidatorService constructor.
     * @param ValidatorRepositoryInterface $validatorRepository
     */
    public function __construct(ValidatorRepositoryInterface $validatorRepository)
    {
        $this->validatorRepository = $validatorRepository;
    }


    /**
     * Get Active Validators Count
     * @return int
     */
    public function getActiveValidatorsCount(): int
    {

        $block = Block::with('validators')->orderByDesc('height')->offset(1)->first();

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
     * @param array $validatorsData
     * @return Collection
     */
    public function createFromAipData(array $validatorsData): Collection
    {
        $validators = [];
        $dateTime = new \DateTime();

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
                    ['public_key' => $validatorPubKey, 'address' => $validatorAddress],
                    $data
                );

                $validators[] = $validator;
            }
        }

        return collect($validators);
    }

    /**
     * @param string $pk
     * @return string
     */
    public function getStake(string $pk): array
    {
        return $this->validatorRepository->getStake($pk);
    }

    /**
     * @return string
     */
    public function getTotalStake(): string
    {
        return $this->validatorRepository->getTotalStake();
    }

    /**
     * @param string $pk
     * @return int
     */
    public function getStatus(string $pk): int
    {
        /** @var Validator $validator */
        $validator = $this->validatorRepository->query()->where('public_key', $pk)->first();
        return $validator->status;
    }

    /**
     * @param string $pk
     * @return array
     */
    public function getDelegatorList(string $pk): Collection
    {
        return $this->validatorRepository->getDelegatorList($pk);
    }
}