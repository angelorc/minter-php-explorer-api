<?php

namespace App\Http\Resources;

use App\Helpers\MathHelper;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

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
class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->resource) {
            $data = [

                'txn' => $this->id,
                'hash' => $this->hash,
                'nonce' => $this->nonce,
                'block' => $this->block->height,
                'timestamp' => $this->block->formattedDate,
                'fee' => $this->feeMnt,
                'type' => $this->typeString,
                'status' => $this->status,
                'payload' => $this->payload,
                'from' => $this->from,
                'data' => []
            ];

            switch ($this->type) {
                case Transaction::TYPE_SEND:
                    $data['data'] = [
                        'to' => $this->to,
                        'coin' => $this->coin,
                        'amount' => isset($this->value) ? MathHelper::makeAmountFromIntString($this->value) : 0,
                    ];
                    break;
                case Transaction::TYPE_SELL_COIN:
                case Transaction::TYPE_SELL_ALL_COIN:
                case Transaction::TYPE_BUY_COIN:

                    $value = $this->value_to_buy ?? $this->value_to_sell ?? 0;

                    $data['data'] = [
                        'coin_to_sell' => $this->coin_to_sell,
                        'coin_to_buy' => $this->coin_to_buy,
                        //TODO: remove when mobile and web will be ready
                        'value' => MathHelper::makeAmountFromIntString($value),
                        'value_to_buy' => isset($this->value_to_buy) ? MathHelper::makeAmountFromIntString($this->value_to_buy) : 0,
                        'value_to_sell' => isset($this->value_to_sell) ? MathHelper::makeAmountFromIntString($this->value_to_sell) : 0,
                    ];
                    break;
                case Transaction::TYPE_CREATE_COIN:
                    $data['data'] = [
                        'name' => $this->name,
                        'symbol' => $this->coin,
                        'initial_amount' => isset($this->initial_amount) ? MathHelper::makeAmountFromIntString($this->initial_amount) : 0,
                        'initial_reserve' => isset($this->initial_reserve) ? MathHelper::makeAmountFromIntString($this->initial_reserve) : 0,
                        'constant_reserve_ratio' => $this->constant_reserve_ratio,
                    ];
                    break;
                case Transaction::TYPE_DECLARE_CANDIDACY:
                    $data['data'] = [
                        'address' => $this->address,
                        'pub_key' => $this->pub_key,
                        'commission' => $this->commission,
                        'coin' => $this->coin,
                        'stake' => isset($this->stake) ? MathHelper::makeAmountFromIntString($this->stake) : 0,
                    ];
                    break;
                case Transaction::TYPE_DELEGATE:
                    $data['data'] = [
                        'pub_key' => $this->pub_key,
                        'coin' => $this->coin,
                        'stake' => isset($this->stake) ? MathHelper::makeAmountFromIntString($this->stake) : 0,
                    ];
                    break;
                case Transaction::TYPE_UNBOUND:
                    $value = $this->value ?? $this->stake ?? 0;
                    $data['data'] = [
                        'pub_key' => $this->pub_key,
                        'coin' => $this->coin,
                        'stake' => MathHelper::makeAmountFromIntString($value),
                    ];
                    break;
                case Transaction::TYPE_REDEEM_CHECK:
                    $data['data'] = [
                        'raw_check' => $this->raw_check,
                        'proof' => $this->proof
                    ];
                    break;
                case Transaction::TYPE_SET_CANDIDATE_ONLINE:
                case Transaction::TYPE_SET_CANDIDATE_OFFLINE:
                    $data['data'] = [
                        'pub_key' => $this->pub_key,
                    ];
                    break;
                default:
                    $data['data'] = [];
                    break;
            }

            //TODO: remove when mobile and web will be ready
            $data['data']['from'] = $this->from ?? '';

            return $data;
        }

        return [];
    }
}