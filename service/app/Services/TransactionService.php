<?php

namespace App\Services;


use App\Helpers\DateTimeHelper;
use App\Models\Coin;
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

        $blockTime = DateTimeHelper::getDateTimeFonNanoSeconds($data['block']['header']['time']);

        foreach ($txs as $tx) {
            try {
                $t = bin2hex(base64_decode($tx));
                $transaction = new Transaction();
                $minterTx = new MinterTx($t);

                $transaction->nonce = $minterTx->nonce;
                $transaction->gas_price = $minterTx->gasPrice;
                $transaction->type = $minterTx->type;
                $transaction->coin = $minterTx->data['coin'] ?? '';
                $transaction->from = $minterTx->from;
                $transaction->to = $minterTx->data['to'] ?? '';
                $transaction->value = $minterTx->data['value'] ?? 0;
                $transaction->hash = $minterTx->getHash();
                $transaction->payload = $minterTx->payload;
                $transaction->fee = $minterTx->getFee();
                $transaction->service_data = $minterTx->serviceData ?? '';
                $transaction->created_at = $blockTime->format('Y-m-d H:i:sO');

                $transactions[] = $transaction;
            } catch (\Exception $exception) {
                Log::channel('transactions')->error(
                    $exception->getFile() . ' ' .
                    $exception->getLine() . ': ' .
                    $exception->getMessage() .
                    ' Block: ' . $data['block']['header']['height'] .
                    ' Transaction: ' . $tx
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
     * @return float
     */
    public function getCommission(\DateTime $startTime = null): float
    {
        return bcmul($this->transactionRepository->get24hTransactionsCommission(), Coin::PIP_STR, 18);
    }

    /**
     * Получить среднюю комиссиию за транзакции с даты
     * @param \DateTime $startTime
     * @return float
     */
    public function getAverageCommission(\DateTime $startTime = null): float
    {
        return bcmul($this->transactionRepository->get24hTransactionsAverageCommission(), Coin::PIP_STR, 18);
    }
}