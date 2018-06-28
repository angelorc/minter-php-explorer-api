<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->resource) {
            $data = [
                'data' => [
                    'hash' => $this->hash,
                    'nonce' => $this->nonce,
                    'block' => $this->block->height,
                    'timestamp' => $this->block->timestamp,
                    'fee' => $this->feeMnt,
                    'type' => $this->typeString,
                    'status' => $this->status,
                    'payload' => $this->payload,
                ]
            ];


            //TODO: как будет поддержка на фронте вернуть
            $data['data']['data'] = [
                'from' => $this->from,
                'to' => $this->to,
                'coin' => $this->coin,
                'amount' => (float)$this->value
            ];

//            switch ($this->type) {
//                case Transaction::TYPE_SEND:
//                    $data['data']['data'] = [
//                        'from' => $this->from,
//                        'to' => $this->to,
//                        'coin' => $this->coin,
//                        'amount' => (float)$this->value
//                    ];
//                    break;
//                case Transaction::TYPE_CONVERT:
//                    $data['data']['data'] = [
//                        'from_coin_symbol' => $this->from_coin_symbol,
//                        'to_coin_symbol' => $this->to_coin_symbol,
//                        'value' => (float)$this->value
//                    ];
//                    break;
//                case Transaction::TYPE_CREATE_COIN:
//                    $data['data']['data'] = [
//                        'name' => $this->name,
//                        'symbol' => $this->symbol,
//                        'initial_amount' => $this->initial_amount,
//                        'initial_reserve' => $this->initial_reserve,
//                        'constant_reserve_ratio' => $this->constant_reserve_ratio,
//                    ];
//                    break;
//                case Transaction::TYPE_DECLARE_CANDIDACY:
//                    $data['data']['data'] = [
//                        'address' => $this->address,
//                        'pub_key' => $this->pub_key,
//                        'commission' => $this->commission,
//                        'coin' => $this->coin,
//                        'stake' => $this->stake
//                    ];
//                    break;
//                case Transaction::TYPE_DELEGATE:
//                    $data['data']['data'] = [
//                        'pub_key' => $this->pub_key,
//                        'coin' => $this->coin,
//                        'stake' => $this->stake
//                    ];
//                    break;
//                case Transaction::TYPE_UNBOND:
//                    $data['data']['data'] = [
//                        'pub_key' => $this->pub_key,
//                        'coin' => $this->coin,
//                        'value' => (float)$this->value
//                    ];
//                    break;
//                case Transaction::TYPE_REDEEM_CHECK:
//                    $data['data']['data'] = [
//                        'raw_check' => $this->raw_check,
//                        'proof' => $this->proof
//                    ];
//                    break;
//                case Transaction::TYPE_SET_CANDIDATE_ONLINE:
//                case Transaction::TYPE_SET_CANDIDATE_OFFLINE:
//                    $data['data']['data'] = [
//                        'pub_key' => $this->pub_key,
//                    ];
//                    break;
//                default:
//                    $data['data']['data'] = [
//
//                    ];
//                    break;
//            }
//
            return $data;
        }

        return [];
    }
}