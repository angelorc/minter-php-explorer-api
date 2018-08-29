<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use App\Models\Coin;
use App\Models\Transaction;
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
     * @TODO: Centralize transaction outputs
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(function ($item) {

                $result = [
                    'txn' => $item->id,
                    'hash' => $item->hash,
                    'nonce' => $item->nonce,
                    'block' => $item->block->height,
                    'timestamp' => $item->block->formattedDate,
                    'fee' => $item->feeMnt,
                    'type' => $item->typeString,
                    'status' => $item->status,
                    'payload' => $item->payload,
                    'data' => []
                ];

                switch ($item->type) {
                    case Transaction::TYPE_SEND:
                        $result['data'] = [
                            'to' => $item->to,
                            'coin' => $item->coin,
                            'amount' => isset($item->value) ? MathHelper::makeAmountFromIntString($item->value) : '',
                        ];
                        break;
                    case Transaction::TYPE_SELL_COIN:
                    case Transaction::TYPE_SELL_ALL_COIN:
                    case Transaction::TYPE_BUY_COIN:
                        $result['data'] = [
                            'coin_to_sell' => $item->coin_to_sell,
                            'coin_to_buy' => $item->coin_to_buy,
                            'value' => isset($item->value) ? MathHelper::makeAmountFromIntString($item->value) : 0,
                            'value_to_buy' => isset($item->value_to_buy) ? MathHelper::makeAmountFromIntString($item->value_to_buy) : 0,
                            'value_to_sell' => isset($item->value_to_sell) ? MathHelper::makeAmountFromIntString($item->value_to_sell) : 0,
                        ];
                        break;
                    case Transaction::TYPE_CREATE_COIN:
                        $result['data'] = [
                            'name' => $item->name,
                            'symbol' => $item->coin,
                            'initial_amount' => isset($item->initial_amount) ? MathHelper::makeAmountFromIntString($item->initial_amount) : 0,
                            'initial_reserve' => isset($item->initial_amount) ? MathHelper::makeAmountFromIntString($item->initial_reserve) : 0,
                            'constant_reserve_ratio' => $item->constant_reserve_ratio,
                        ];
                        break;
                    case Transaction::TYPE_DECLARE_CANDIDACY:
                        $result['data'] = [
                            'address' => $item->address,
                            'pub_key' => $item->pub_key,
                            'commission' => $item->commission,
                            'coin' => $item->coin,
                            'stake' => isset($item->stake) ?  MathHelper::makeAmountFromIntString($item->stake) : ''
                        ];
                        break;
                    case Transaction::TYPE_DELEGATE:
                    case Transaction::TYPE_UNBOUND:
                        $result['data'] = [
                            'pub_key' => $item->pub_key,
                            'coin' => $item->coin,
                            'stake' => isset($item->stake) ?  MathHelper::makeAmountFromIntString($item->stake) : ''
                        ];
                        break;
                    case Transaction::TYPE_REDEEM_CHECK:
                        $result['data'] = [
                            'raw_check' => $item->raw_check,
                            'proof' => $item->proof
                        ];
                        break;
                    case Transaction::TYPE_SET_CANDIDATE_ONLINE:
                    case Transaction::TYPE_SET_CANDIDATE_OFFLINE:
                        $result['data'] = [
                            'pub_key' => $item->pub_key,
                        ];
                        break;
                    default:
                        $result['data'] = [];
                        break;
                }
                $result['data']['from'] = $item->from ?? '';

                return $result;
            }),
        ];
    }
}