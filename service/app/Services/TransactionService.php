<?php

namespace App\Services;


use App\Helpers\DateTimeHelper;
use App\Models\Coin;
use App\Models\Transaction;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\StringHelper;

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

        $txs = $data['transactions'];

        $blockTime = DateTimeHelper::getDateTimeFonNanoSeconds($data['time']);

        foreach ($txs as $tx) {
            try {
                $transaction = new Transaction();

                $transaction->type = $tx['type'];
                $transaction->nonce = $tx['nonce'];
                $transaction->hash = $tx['hash'];
                $transaction->gas_price = $tx['gasPrice'];
                $transaction->fee = $tx['gas'];
                $transaction->payload = $tx['payload'] ?? null;
                $transaction->service_data = $tx['serviceData'] ?? null;
                $transaction->from = StringHelper::mb_ucfirst($tx['from']);
                $transaction->created_at = $blockTime->format('Y-m-d H:i:sO');

                $val = $tx['data']['value'] ?? 0;
                $transaction->value = bcmul($val, Coin::PIP_STR, 18);
                $transaction->coin = mb_strtoupper($tx['data']['coin'] ?? '');
                $transaction->to = StringHelper::mb_ucfirst($tx['data']['to'] ?? '');

                $pubKey = $tx['data']['pubkey'] ?? null;
                $transaction->pub_key = $pubKey ? StringHelper::mb_ucfirst($pubKey) : null;

                if ($transaction->type === Transaction::TYPE_SELL_COIN && $transaction->type === Transaction::TYPE_BUY_COIN) {
                    $transaction->coin_to_sell = mb_strtoupper($tx['data']['coin_to_sell'] ?? '');
                    $transaction->coin_to_buy = mb_strtoupper($tx['data']['coin_to_buy'] ?? '');
                }
                if ($transaction->type === Transaction::TYPE_SELL_COIN) {
                    $transaction->value = $tx['data']['value_to_sell'];
                }
                if ($transaction->type === Transaction::TYPE_BUY_COIN) {
                    $transaction->value = $tx['data']['value_to_buy'];
                }

                if ($transaction->type === Transaction::TYPE_CREATE_COIN) {
                    $transaction->name = mb_strtoupper($tx['data']['name'] ?? '');
                    $transaction->symbol = mb_strtoupper($tx['data']['coin_symbol'] ?? '');
                    $transaction->initial_amount = $tx['data']['initial_amount'] ?? null;
                    $transaction->initial_reserve = $tx['data']['initial_reserve'] ?? null;
                    $transaction->constant_reserve_ratio = $tx['data']['constant_reserve_ratio'] ?? null;
                }

                if ($transaction->type === Transaction::TYPE_DECLARE_CANDIDACY) {
                    $address = $tx['data']['Address'] ?? null;
                    $transaction->address = $address ? StringHelper::mb_ucfirst($address) : null;
                    $transaction->commission = $tx['data']['Commission'] ?? null;
                }

                if (\in_array($transaction->type, [Transaction::TYPE_DECLARE_CANDIDACY, Transaction::TYPE_DELEGATE],
                    true)) {
                    $pubKey = $tx['data']['PubKey'] ?? null;
                    $transaction->pub_key = $pubKey ? StringHelper::mb_ucfirst($pubKey) : null;
                    $transaction->coin = mb_strtoupper($tx['data']['Coin'] ?? '');
                    $transaction->stake = $tx['data']['Stake'] ?? null;
                }

                if (isset($tx['tx_result']['code'])) {
                    $transaction->status = false;
                    $transaction->log = $tx['tx_result']['log'] ?? null;
                } else {
                    $transaction->status = true;
                    $transaction->gas_wanted = $tx['tx_result']['gas_wanted'] ?? null;
                    $transaction->gas_used = $tx['tx_result']['gas_used'] ?? null;
                }

                $transactions[] = $transaction;

            } catch (\Exception $exception) {
                Log::channel('transactions')->error(
                    $exception->getFile() . ' ' .
                    $exception->getLine() . ': ' .
                    $exception->getMessage() .
                    ' Block: ' . $data['height'] .
                    ' Transaction: ' . $tx['hash']
                );
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
     * Скорость обработки транзакций
     * @return float
     */
    public function getTransactionsSpeed(): float
    {
        return round($this->get24hTransactionsCount() / (24 * 3600), 8);
    }

    /**
     * Количество транзакций за последние 24 часа
     * @return int
     */
    public function get24hTransactionsCount(): int
    {
        $count = Cache::get('24hTransactionsCount', null);

        if (!$count) {
            $count = $this->transactionRepository->get24hTransactionsCount();
            Cache::put('24hTransactionsCount', $count, 1);
        }

        return $count;
    }

    /**
     * Получить сумму комиссии за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getCommission(\DateTime $startTime = null): string
    {
        return bcmul($this->transactionRepository->get24hTransactionsCommission(), Coin::PIP_STR, 18);
    }

    /**
     * Получить среднюю комиссиию за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getAverageCommission(\DateTime $startTime = null): string
    {
        return bcmul($this->transactionRepository->get24hTransactionsAverageCommission(), Coin::PIP_STR, 18);
    }
}