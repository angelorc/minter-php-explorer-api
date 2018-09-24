<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventChartCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(function ($item) {
                $result = [
                    'time' => $item->time,
                    'amount' => MathHelper::makeAmountFromIntString($item->amount),
                ];
                return $result;
            })->toArray()
        ];
    }
}