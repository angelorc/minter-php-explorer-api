<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CoinsResource extends JsonResource
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
            'symbol' => $this->symbol,
            'name' => $this->name,
            'crr' => $this->crr,
            'reserveBalance' => $this->reserve_balance,
            'volume' => $this->volume,
        ];
    }
}