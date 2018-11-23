<?php

namespace App\Repository;

use Illuminate\Support\Collection;

interface ValidatorRepositoryInterface extends ModelRepositoryInterface
{
    public function getStake(string $pk);

    public function getTotalStake(): string;

    public function getDelegatorList(string $pk): Collection;
}