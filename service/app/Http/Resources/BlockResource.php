<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use App\Models\Block;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *     definition="BlockMetaData",
 *     type="object",
 *
 *     @SWG\Property(property="current_page", type="integer", example="1"),
 *     @SWG\Property(property="from",         type="integer", example="2"),
 *     @SWG\Property(property="last_page",    type="integer", example="4"),
 *     @SWG\Property(property="path",         type="string",  example="http://localhost:8000/api/v1/blocks"),
 *     @SWG\Property(property="per_page",     type="integer", example="50"),
 *     @SWG\Property(property="to",           type="integer", example="50"),
 *     @SWG\Property(property="total",        type="integer", example="130")
 * )
 **/

/**
 * @SWG\Definition(
 *     definition="BlockLinksData",
 *     type="object",
 *
 *     @SWG\Property(property="first", type="string", example="http://localhost:8000/api/v1/blocks?page=1"),
 *     @SWG\Property(property="last",  type="string", example="http://localhost:8000/api/v1/blocks?page=2"),
 *     @SWG\Property(property="prev",  type="string", example="null"),
 *     @SWG\Property(property="next",  type="string", example="http://localhost:8000/api/v1/blocks?page=2")
 * )
 **/
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
            'blockTime' => floor($this->block_time),
            'validators' => ValidatorResource::collection($this->validators)
        ];
    }
}