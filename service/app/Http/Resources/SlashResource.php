<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class SlashResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'block' => $this->block_height,
            'coin' => $this->coin,
            'amount' => MathHelper::makeAmountFromIntString($this->amount),
            'address' => $this->address,
            'validator' => $this->validator_pk,
            'timestamp' => $this->block->formattedDate
        ];
    }
}