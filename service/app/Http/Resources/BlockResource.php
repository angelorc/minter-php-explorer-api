<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use App\Models\Block;
use App\Models\Coin;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    protected $latestBlockHeight;

    public function __construct(Block $resource, int $latestBlockHeight = 0)
    {
        parent::__construct($resource);
        $this->latestBlockHeight = $latestBlockHeight;
    }


    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'latestBlockHeight' => $this->latestBlockHeight,
            'height' => $this->height,
            'timestamp' => $this->formattedDate,
            'txCount' => $this->tx_count,
            'reward' => MathHelper::makeAmountFromIntString($this->block_reward),
            'size' => $this->size,
            'hash' => $this->hash,
            'validators' => ValidatorResource::collection($this->validators)
        ];
    }
}