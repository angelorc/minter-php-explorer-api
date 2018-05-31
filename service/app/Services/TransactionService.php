<?php

namespace App\Services;


use App\Models\Transaction;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Minter\SDK\MinterTx;

class TransactionService implements TransactionServiceInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * TransactionService constructor.
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }


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

            try{
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
                $transaction->payload = $minterTx->payload;

                //TODO: как появится админка с валидаторами поменять
                $transaction->validator_id = 1;

                $transactions[] = $transaction;
            }catch (\Exception $exception){
                Log::error($exception->getMessage());
            }
        }

        return collect($transactions);
    }

    /**
     * Количество транзакций
     * @param string|null $address
     * @return int
     */
    public function getTotalTransactionsCount(string $address = null): int
    {
        return $this->transactionRepository->getTransactionsCount($address);
    }

    /**
     * Количество транзакций за последние 24 часа
     * @return int
     */
    public function get24hTransactionsCount(): int
    {
        $count = Cache::get('24hTransactionsCount', null);

        if (!$count){

            $count = $this->transactionRepository->get24hTransactionsCount();

            Cache::put('24hTransactionsCount', $count, 1);
        }

       return $count;
    }

    /**
     * Скорость обработки транзакций
     * @return float
     */
    public function getTransactionsSpeed(): float
    {
        return round($this->get24hTransactionsCount() / (24 * 3600), 8);
    }

    /**
     * Получить сумму комиссии за транзакции с даты
     * @param \DateTime $startTime
     * @return float
     */
    public function getCommission(\DateTime $startTime = null): float
    {
        $transactions = $this->transactionRepository->get24hTransactions();

        if($transactions->count()){
            return $transactions->reduce(function ($carry, $transaction) {
                /** @var Transaction $transaction */
                return $carry + $transaction->fee;
            });
        }

        return 0;
    }

    /**
     * Получить среднюю комиссиию за транзакции с даты
     * @param \DateTime $startTime
     * @return float
     */
    public function getAverageCommission(\DateTime $startTime = null): float
    {

        $transactions = $this->transactionRepository->get24hTransactions();

        $fee = $transactions->reduce(function ($carry, $transaction) {
            /** @var Transaction $transaction */
            return $carry + $transaction->fee;
        });

        if ($fee){
            return $transactions->count() / $fee;
        }

        return 0;
    }
}