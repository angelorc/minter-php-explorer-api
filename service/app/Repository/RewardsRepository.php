<?php

namespace App\Repository;


use App\Models\Reward;

class RewardsRepository extends ModelRepository implements RewardsRepositoryInterface
{

    /**
     * RewardsRepository constructor.
     * @param Reward $model
     */
    public function __construct(Reward $model)
    {
        $this->model = $model;
    }
}