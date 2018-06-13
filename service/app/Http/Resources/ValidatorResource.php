<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ValidatorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
        ];
    }
}