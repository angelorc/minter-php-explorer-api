<?php

namespace App\Services;


use App\Models\Transaction;
use Illuminate\Support\Collection;
use Minter\SDK\MinterTx;

class TransactionService implements TransactionServiceInterface
{

    /**
     * Получить колекцию транзакций из данных API
     * @param array $data
     * @return Collection
     * @throws \Exception
     */
    public function decodeTransactionsFromApiData(array $data): Collection
    {

        $transactions = [];

        $txs = $data['block']['data']['txs'];

        foreach ($txs as $tx) {

            $t = 'Mx' . bin2hex(base64_decode($tx));
            $transaction = new Transaction();
            $minterTx = new MinterTx($t);

            $transaction->nonce = $minterTx->nonce;
            $transaction->gas_price = $minterTx->gasPrice;
            $transaction->type = $minterTx->type;
            $transaction->coin = $minterTx->data['coin'];
            $transaction->from = $minterTx->from;
            $transaction->to = $minterTx->data['to'];
            $transaction->value = $minterTx->data['value'];
            $transaction->hash = bin2hex(base64_decode($tx));

            //TODO: как появится админка с валидаторами поменять
            $transaction->validator_id = 1;

            $transactions[] = $transaction;
        }

        return collect($transactions);
    }

}