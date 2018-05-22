<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @SWG\Definition(
 *     definition="TransactionMetaData",
 *     type="object",
 *
 *     @SWG\Property(property="current_page", type="integer", example="1"),
 *     @SWG\Property(property="from",         type="integer", example="2"),
 *     @SWG\Property(property="last_page",    type="integer", example="4"),
 *     @SWG\Property(property="path",         type="string",  example="http://localhost:8000/api/v1/transactions"),
 *     @SWG\Property(property="per_page",     type="integer", example="50"),
 *     @SWG\Property(property="to",           type="integer", example="50"),
 *     @SWG\Property(property="total",        type="integer", example="130")
 * )
 **/
/**
 * @SWG\Definition(
 *     definition="TransactionLinksData",
 *     type="object",
 *
 *     @SWG\Property(property="first", type="string", example="http://localhost:8000/api/v1/transactions?page=1"),
 *     @SWG\Property(property="last",  type="string", example="http://localhost:8000/api/v1/transactions?page=2"),
 *     @SWG\Property(property="prev",  type="string", example="null"),
 *     @SWG\Property(property="next",  type="string", example="http://localhost:8000/api/v1/transactions?page=2")
 * )
 **/
class TransactionCollection extends ResourceCollection
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
                   'hash' => $item->hash,
                   'nonce' => $item->nonce,
                   'block' => $item->block->height,
                   'timestamp' => $item->block->timestamp,
                   'fee' => $item->fee,
                   'type' => $item->typeString,
                   'status' => $item->status,
                   'data' => [
                       'from' => $item->from,
                       'to' => $item->from,
                       'coin' => $item->coin,
                       'amount' => (float)$item->value
                   ]
               ];
            }),
        ];
    }
}