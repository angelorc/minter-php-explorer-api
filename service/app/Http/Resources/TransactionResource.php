<?php

namespace App\Http\Resources;

use App\Models\Coin;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        if ($this->resource) {
            $data = [
                'data' => [
                    'txn' => $this->id,
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

            switch ($this->type) {
                case Transaction::TYPE_SEND:
                    $data['data']['data'] = [
                        'to' => $this->to,
                        'coin' => $this->coin,
                        'amount' => (float)$this->value
                    ];
                    break;
                case Transaction::TYPE_SELL_COIN:
                case Transaction::TYPE_BUY_COIN:
                    $result['data'] = [
                        'coin_to_sell' => $this->coin_to_sell,
                        'coin_to_buy' => $this->coin_to_buy,
                        'value' => (float)$this->value
                    ];
                    break;
                case Transaction::TYPE_CREATE_COIN:
                    $data['data']['data'] = [
                        'name' => $this->name,
                        'symbol' => $this->symbol,
                        'initial_amount' => $this->initial_amount,
                        'initial_reserve' => $this->initial_reserve,
                        'constant_reserve_ratio' => $this->constant_reserve_ratio,
                    ];
                    break;
                case Transaction::TYPE_DECLARE_CANDIDACY:
                    $data['data']['data'] = [
                        'address' => $this->address,
                        'pub_key' => $this->pub_key,
                        'commission' => $this->commission,
                        'coin' => $this->coin,
                        'stake' => bcmul($this->stake, Coin::PIP_STR, 18)
                    ];
                    break;
                case Transaction::TYPE_DELEGATE:
                    $data['data']['data'] = [
                        'pub_key' => $this->pub_key,
                        'coin' => $this->coin,
                        'stake' => bcmul($this->stake, Coin::PIP_STR, 18)
                    ];
                    break;
                case Transaction::TYPE_UNBOUND:
                    $data['data']['data'] = [
                        'pub_key' => $this->pub_key,
                        'coin' => $this->coin,
                        'stake' => bcmul($this->value, Coin::PIP_STR, 18)
                    ];
                    break;
                case Transaction::TYPE_REDEEM_CHECK:
                    $data['data']['data'] = [
                        'raw_check' => $this->raw_check,
                        'proof' => $this->proof
                    ];
                    break;
                case Transaction::TYPE_SET_CANDIDATE_ONLINE:
                case Transaction::TYPE_SET_CANDIDATE_OFFLINE:
                    $data['data']['data'] = [
                        'pub_key' => $this->pub_key,
                    ];
                    break;
                default:
                    $data['data']['data'] = [];
                    break;
            }

            $data['data']['data']['from'] = $this->from ?? '';

            return $data;
        }

        return [];
    }
}