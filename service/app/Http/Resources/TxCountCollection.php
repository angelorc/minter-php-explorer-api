<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TxCountCollection extends ResourceCollection
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => array_reverse($this->collection->map(function ($item) {
                return [
                    'date' => $item->date,
                    'txCount' => $item->transactions_count,
                ];
            })->toArray())
        ];
    }
}