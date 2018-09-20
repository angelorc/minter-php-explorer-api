<?php

namespace App\Repository;


use App\Models\Slash;

class SlashesRepository extends ModelRepository implements SlashesRepositoryInterface
{
    /**
     * RewardsRepository constructor.
     * @param Slash $model
     */
    public function __construct(Slash $model)
    {
        $this->model = $model;
    }
}