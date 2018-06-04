<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->resource) {
            return [
                'data' => [
                    'hash' => $this->hash,
                    'nonce' => $this->nonce,
                    'block' => $this->block->height,
                    'timestamp' => $this->block->timestamp,
                    'fee' => $this->feeMnt,
                    'type' => $this->typeString,
                    'status' => $this->status,
                    'payload' => $this->payload,
                    'data' => [
                        'from' => $this->from,
                        'to' => $this->to,
                        'coin' => $this->coin,
                        'amount' => (float)$this->value
                    ]
                ]
            ];
        }

        return [];
    }
}