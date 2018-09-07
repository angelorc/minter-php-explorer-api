<?php

namespace App\Traits;


use App\Helpers\StringHelper;
use App\Models\Transaction;
use App\Models\TxTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait TransactionTrait
{
    /**
     * @param array $tx
     * @param int $blockHeight
     * @param \DateTime $blockTime
     * @return Transaction|null
     */
    public function createTransactionFromApiData(array $tx, int $blockHeight, \DateTime $blockTime): ?Transaction
    {
        try {
            $transaction = new Transaction();
            $transaction->block_id = $blockHeight;
            $transaction->hash = StringHelper::mb_ucfirst($tx['hash']);
            $transaction->from = StringHelper::mb_ucfirst($tx['from']);
            $transaction->nonce = $tx['nonce'];
            $transaction->gas_price = $tx['gas_price'];
            $transaction->type = $tx['type'];
            $transaction->payload = $tx['payload'] ?? null;
            $transaction->fee = $tx['gas'] ?? 0;
            $transaction->service_data = $tx['serviceData'] ?? null;
            $transaction->created_at = $blockTime->format('Y-m-d H:i:sO');
            $transaction->gas_coin = $tx['gas_coin'] ?? null;

            $payload = strip_tags(base64_decode($tx['payload']));
            $transaction->payload = '' !== $payload ? $payload : null;

            if (isset($tx['tx_result']['code'])) {
                $transaction->status = false;
                $transaction->log = $tx['tx_result']['log'] ?? null;
            } else {
                $transaction->status = true;
                $transaction->gas_wanted = $tx['tx_result']['gas_wanted'] ?? null;
                $transaction->gas_used = $tx['tx_result']['gas_used'] ?? null;
            }

            switch ($transaction->type) {
                case Transaction::TYPE_SEND:
                    $transaction->coin = mb_strtoupper($tx['data']['coin'] ?? '');
                    $transaction->to = StringHelper::mb_ucfirst($tx['data']['to'] ?? '');
                    $transaction->value = $tx['data']['value'] ?? null;
                    break;
                case Transaction::TYPE_CREATE_COIN:
                    $transaction->name = $tx['data']['name'] ?? null;
                    $transaction->coin = mb_strtoupper($tx['data']['coin_symbol']);
                    $transaction->initial_amount = $tx['data']['initial_amount'] ?? null;
                    $transaction->initial_reserve = $tx['data']['initial_reserve'] ?? null;
                    $transaction->constant_reserve_ratio = $tx['data']['constant_reserve_ratio'] ?? null;
                    break;
                case Transaction::TYPE_DECLARE_CANDIDACY:
                    $transaction->address = StringHelper::mb_ucfirst($tx['data']['address']);
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->commission = $tx['data']['commission'] ?? null;
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->stake = $tx['data']['stake'] ?? null;
                    break;
                case Transaction::TYPE_DELEGATE:
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->stake = $tx['data']['stake'] ?? null;
                    break;
                case Transaction::TYPE_UNBOUND:
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->value = $tx['data']['value'] ?? 0;
                    break;
                case Transaction::TYPE_REDEEM_CHECK:
                    $transaction->raw_check = $tx['data']['raw_check'] ?? null;
                    $transaction->proof = $tx['data']['proof'] ?? null;
                    break;
                case Transaction::TYPE_SET_CANDIDATE_ONLINE:
                case Transaction::TYPE_SET_CANDIDATE_OFFLINE:
                    $pk = $tx['data']['pubkey'] ?? $tx['data']['pub_key'];
                    $transaction->pub_key = StringHelper::mb_ucfirst($pk);
                    break;
            }

            if (
                $transaction->type === Transaction::TYPE_SELL_COIN ||
                $transaction->type === Transaction::TYPE_SELL_ALL_COIN ||
                $transaction->type === Transaction::TYPE_BUY_COIN) {
                $transaction->coin_to_sell = mb_strtoupper($tx['data']['coin_to_sell']);
                $transaction->coin_to_buy = mb_strtoupper($tx['data']['coin_to_buy']);
            }
            if ($transaction->type === Transaction::TYPE_SELL_COIN) {
                $transaction->value_to_sell = $tx['data']['value_to_sell'] ?? 0;
                $transaction->value_to_buy = $this->getValueFromTxTag($tx['tx_result']['tags']) ?? 0;
            }
            if ($transaction->type === Transaction::TYPE_SELL_ALL_COIN) {
                $transaction->value_to_sell = $this->getValueFromTxTag($tx['tx_result']['tags']) ?? 0;
            }
            if ($transaction->type === Transaction::TYPE_BUY_COIN) {
                $transaction->value_to_buy = $tx['data']['value_to_buy'] ?? 0;
                $transaction->value_to_sell = $this->getValueFromTxTag($tx['tx_result']['tags']) ?? 0;
            }

            $tags = null;
            if (isset($tx['tx_result']['tags'])) {
                $tags = $this->decodeTxTags($tx['tx_result']['tags']);
            }

            $transaction->save();
            if ($tags) {
                $transaction->tags()->saveMany($tags);
            }

            return $transaction;

        } catch (\Exception $exception) {
            Log::channel('transactions')->error(
                $exception->getFile() . ' ' .
                $exception->getLine() . ': ' .
                $exception->getMessage() .
                ' Block: ' . $blockHeight .
                ' Transaction: ' . $tx['hash']
            );
            return null;
        }
    }


    /**
     * @param array $tagsData
     * @return Collection
     */
    private function decodeTxTags(array $tagsData): Collection
    {
        return collect(array_map(function ($el) {
            $tag = new TxTag();
            $tag->key = base64_decode($el['key']);
            $tag->value = '';
            try {
                $tag->value = base64_decode($el['value']);
            } catch (\Exception $exception) {
                Log::channel('transactions')->error(
                    $exception->getFile() . ' ' .
                    $exception->getLine() . 'Tag decode: ' .
                    $exception->getMessage()
                );
            }
            return $tag;
        }, $tagsData));
    }

    /**
     * @param array $tagsData
     * @return null|string
     */
    private function getValueFromTxTag(array $tagsData): ?string
    {
        $tags = $this->decodeTxTags($tagsData);
        foreach ($tags as $tag) {
            if ($tag->key === 'tx.return') {
                return $tag->value;
            }
        }
        return null;
    }
}