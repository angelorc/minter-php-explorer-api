<?php

namespace App\Repository;

use App\Helpers\MathHelper;
use App\Models\Delegator;
use App\Models\Transaction;
use App\Models\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ValidatorRepository extends ModelRepository implements ValidatorRepositoryInterface
{
    /**
     * RewardsRepository constructor.
     * @param Validator $model
     */
    public function __construct(Validator $model)
    {
        $this->model = $model;
    }


    /**
     * @param string $pk
     * @return array
     */
    public function getStake(string $pk): array
    {
        $result = DB::select('
            select count(id), sum(stake) as stake
            from transactions
            where pub_key >= :pk ;
        ', ['pk' => $pk]);

        return [
            'count' => $result[0]->count,
            'stake' => $result[0]->stake,
        ];
    }

    /**
     * @return string
     */
    public function getTotalStake(): string
    {
        $result = DB::select('
            select sum(stake) as stake
            from transactions
        ');

        return $result[0]->stake;
    }

    public function getDelegatorList(string $pk): Collection
    {
        $result = DB::select('
            select  coin, "from" as address, sum(stake) as value
            from transactions
            where type = :type and pub_key = :pk
            group by coin, "from"
        ', ['pk' => $pk, 'type' => Transaction::TYPE_DELEGATE]);

        $data = collect([]);

        foreach ($result as $item) {
            $delegator = new Delegator();
            $delegator->coin = $item->coin;
            $delegator->address = $item->address;
            $delegator->value = MathHelper::makeAmountFromIntString($item->value);
            $data->push($delegator);
        }

        return $data;
    }
}