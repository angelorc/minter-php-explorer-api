<?php

namespace App\Traits;


use App\Helpers\LogHelper;
use App\Helpers\StringHelper;
use App\Jobs\CreateCoinFromTransactionJob;
use App\Models\Transaction;
use App\Models\TxTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

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
            $transaction->gas_wanted = $tx['gas_wanted'] ?? null;
            $transaction->gas_used = $tx['gas_used'] ?? null;
            $payload = strip_tags(base64_decode($tx['payload']));
            $transaction->payload = '' !== $payload ? $payload : null;
            $transaction->status = true;
            $transaction->log = $tx['log'] ?? null;

            if (isset($tx['code'])) {
                $transaction->status = false;
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
                $transaction->value_to_buy = $this->getValueFromTxTag($tx['tags']) ?? 0;
            }
            if ($transaction->type === Transaction::TYPE_SELL_ALL_COIN) {
                $transaction->value_to_buy = $this->getValueFromTxTag($tx['tags']) ?? 0;
                $transaction->value_to_sell = $this->getValueFromTxTag($tx['tags'], 'tx.sell_amount') ?? 0;
            }
            if ($transaction->type === Transaction::TYPE_BUY_COIN) {
                $transaction->value_to_buy = $tx['data']['value_to_buy'] ?? 0;
                $transaction->value_to_sell = $this->getValueFromTxTag($tx['tags']) ?? 0;
            }

            $tags = null;
            if (isset($tx['tags'])) {
                $tags = $this->getTxTags($tx['tags']);
            }

            $transaction->save();
            if ($tags) {
                $transaction->tags()->saveMany($tags);
            }

            if ($transaction->type === Transaction::TYPE_CREATE_COIN) {
                Queue::pushOn('main', new CreateCoinFromTransactionJob($transaction));
            }

            return $transaction;

        } catch (\Exception $exception) {
            LogHelper::transactionsError($exception, $blockHeight, $tx['hash']);
            return null;
        }
    }

    /**
     * @param array $tagsData
     * @return Collection
     */
    private function getTxTags(array $tagsData): Collection
    {
        $result = [];

        foreach ($tagsData as $k => $v) {
            $tag = new TxTag();
            $tag->key = base64_decode($k);
            $tag->value = $v;
        }

        return collect($result);
    }

    /**
     * @param array $tagsData
     * @param string $tagName
     * @return null|string
     */
    private function getValueFromTxTag(array $tagsData, string $tagName = 'tx.return'): ?string
    {
        foreach ($tagsData as $k => $v) {
            if ($k === $tagName) {
                return $v;
            }
        }
        return null;
    }
}