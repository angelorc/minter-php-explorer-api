<?php

namespace App\Repository;


use App\Helpers\StringHelper;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;

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

    /**
     * @param string $address
     * @param string $scale
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return array
     */
    public function getChartData(string $address, string $scale, \DateTime $startTime, \DateTime $endTime): array
    {
        $result = DB::select('
            select sum (r.amount) as amount,  date_trunc(:scale, b.created_at) as time
            FROM rewards r
               left join blocks b on b.height = r.block_height
            where r.address = :address AND b.created_at >= :start AND b.created_at <= :end
            group by date_trunc(:scale, b.created_at)
            order by time
        ', [
            'scale' => $scale,
            'address' => StringHelper::mb_ucfirst($address),
            'start' => $startTime->format('Y-m-d H:i:s'),
            'end' => $endTime->format('Y-m-d H:i:s'),
        ]);

        return $result;
    }
}