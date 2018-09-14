<?php

namespace App\Services;

use App\Helpers\CoinHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\MathHelper;
use App\Jobs\StoreTransactionJob;
use App\Repository\TransactionRepositoryInterface;
use App\Traits\TransactionTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

class TransactionService implements TransactionServiceInterface
{

    use TransactionTrait;

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    /** @var CoinServiceInterface */
    protected $coinService;

    /**
     * TransactionService constructor.
     * @param TransactionRepositoryInterface $transactionRepository
     * @param CoinServiceInterface $coinService
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository, CoinServiceInterface $coinService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->coinService = $coinService;
    }

    /**
     * Create transactions from API data
     * @param array $data
     * @return Collection
     * @throws \Exception
     */
    public function createFromAipData(array $data): Collection
    {
        $transactions = [];
        $txs = $data['transactions'];
        $blockTime = DateTimeHelper::parse($data['time']) ?? new \DateTime();

        foreach ($txs as $tx) {
            $transaction = $this->createTransactionFromApiData($tx, $data['height'], $blockTime);
            if ($transaction) {
                $transactions[] = $transaction;
            }
        }
        return collect($transactions);
    }

    /**
     * Push transactions to queue
     * @param array $data
     * @return void
     */
    public function createFromAipDataAsync(array $data): void
    {
        $txs = $data['transactions'];
        $blockTime = DateTimeHelper::parse($data['time']) ?? new \DateTime();

        foreach ($txs as $tx) {
            Queue::pushOn('transactions', new StoreTransactionJob($tx, $data['height'], $blockTime));
        }
    }

    /**
     * Total transactions
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
        return $this->transactionRepository->get24hTransactionsCount();
    }

    /**
     * Получить сумму комиссии за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getCommission(\DateTime $startTime = null): string
    {
        return MathHelper::makeCommissionFromIntString($this->transactionRepository->get24hTransactionsCommission());
    }

    /**
     * Получить среднюю комиссиию за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getAverageCommission(\DateTime $startTime = null): string
    {
        return MathHelper::makeCommissionFromIntString($this->transactionRepository->get24hTransactionsAverageCommission());
    }

    /**
     * Данные по трнзакциям за 24 часа
     * @return array
     */
    public function get24hTransactionsData(): array
    {
        $data = $this->transactionRepository->get24hTransactionsData();

        return [
            'count' => $data['count'],
            'perSecond' => round($data['count'] / 86400, 8),
            'sum' => CoinHelper::convertUnitToMnt($data['sum']),
            'avg' => CoinHelper::convertUnitToMnt($data['avg']),
        ];
    }

    /**
     * @param array $txTags
     */
    public function saveTransactionsTags(array $txTags): void
    {
        foreach ($txTags as $hash => $tags) {
            $transaction = $this->transactionRepository->findByHash($hash);

            if ($transaction) {
                $this->transactionRepository->saveTransactionTags($transaction, $tags);
            }
        }
    }


}