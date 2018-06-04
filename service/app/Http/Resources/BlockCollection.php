<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

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

class BlockCollection extends ResourceCollection
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
            'data'  => $this->collection->map(function ($item) {
                return [
                    'latestBlockHeight' => $item->latestBlockHeight,
                    'height' => $item->height,
                    'timestamp' => $item->timestamp,
                    'txCount' => $item->tx_count,
                    'reward' => $item->block_reward,
                    'size' => $item->size,
                    'hash' => $item->hash,
                    'blockTime' => $item->block_time,
                    'validators' => isset($this->validators) ? ValidatorResource::collection($this->validators) : []
                ];
            }),
        ];
    }
}