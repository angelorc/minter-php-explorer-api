<?php

namespace App\Services;

use App\Helpers\CoinHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\MathHelper;
use App\Helpers\StringHelper;
use App\Models\Transaction;
use App\Models\TxTag;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        $blockTime = DateTimeHelper::getDateTimeFromNanoSeconds($data['time']);

        foreach ($txs as $tx) {
            try {
                $transaction = new Transaction();
                $transaction->hash = StringHelper::mb_ucfirst($tx['hash']);
                $transaction->from = StringHelper::mb_ucfirst($tx['from']);
                $transaction->nonce = $tx['nonce'];
                $transaction->gas_price = $tx['gas_price'];
                $transaction->type = $tx['type'];
                $transaction->payload = $tx['payload'] ?? null;
                $transaction->fee = $tx['gas'] ?? 0;
                $transaction->service_data = $tx['serviceData'] ?? null;
                $transaction->created_at = $blockTime->format('Y-m-d H:i:sO');
                $transaction->value = 0;
                $transaction->gas_coin = $tx['gas_coin'] ?? null;

                try{
                    $payload = strip_tags(base64_decode($tx['payload']));
                    $transaction->payload = \mb_strlen($payload) ? $payload : null ;
                }catch (\Exception $exception){
                    Log::channel('transactions')->error(
                        $exception->getFile() . ' ' .
                        $exception->getLine() . 'Payload decode: ' . $tx['payload'] . ' ' .
                        $exception->getMessage()
                    );
                }

                if (isset($tx['tx_result']['code'])) {
                    $transaction->status = false;
                    $transaction->log = $tx['tx_result']['log'] ?? null;
                } else {
                    $transaction->status = true;
                    $transaction->gas_wanted = $tx['tx_result']['gas_wanted'] ?? null;
                    $transaction->gas_used = $tx['tx_result']['gas_used'] ?? null;
                }

                if ($transaction->type === Transaction::TYPE_SEND) {
                    $transaction->coin = mb_strtoupper($tx['data']['coin'] ?? '');
                    $transaction->to = StringHelper::mb_ucfirst($tx['data']['to'] ?? '');
                    $transaction->value = $tx['data']['value'] ?? null;
                }

                if (
                    $transaction->type === Transaction::TYPE_SELL_COIN ||
                    $transaction->type === Transaction::TYPE_SELL_ALL_COIN ||
                    $transaction->type === Transaction::TYPE_BUY_COIN) {
                    $transaction->coin_to_sell = mb_strtoupper($tx['data']['coin_to_sell']);
                    $transaction->coin_to_buy = mb_strtoupper($tx['data']['coin_to_buy']);
                }
                if ($transaction->type === Transaction::TYPE_SELL_COIN) {
                    $transaction->value = $tx['data']['value_to_sell'] ?? 0;
                }
                if ($transaction->type === Transaction::TYPE_SELL_ALL_COIN) {
                    $transaction->value = $this->getValueFromTxTag($tx['tx_result']['tags']) ?? 0;
                }
                if ($transaction->type === Transaction::TYPE_BUY_COIN) {
                    $transaction->value = $tx['data']['value_to_buy'] ?? 0;
                }

                if ($transaction->type === Transaction::TYPE_CREATE_COIN) {
                    $transaction->name = $tx['data']['name'] ?? null;
                    $transaction->coin = mb_strtoupper($tx['data']['coin_symbol']);
                    $transaction->initial_amount = $tx['data']['initial_amount'] ?? null;
                    $transaction->initial_reserve = $tx['data']['initial_reserve'] ?? null;
                    $transaction->constant_reserve_ratio = $tx['data']['constant_reserve_ratio'] ?? null;
                }

                if ($transaction->type === Transaction::TYPE_DECLARE_CANDIDACY) {
                    $transaction->address = StringHelper::mb_ucfirst($tx['data']['address']);
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->commission = $tx['data']['commission'] ?? null;
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->stake = $tx['data']['stake'] ?? null;
                }

                if ($transaction->type === Transaction::TYPE_DELEGATE) {
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->stake = $tx['data']['stake'] ?? null;
                }

                if ($transaction->type === Transaction::TYPE_UNBOUND) {
                    $transaction->pub_key = StringHelper::mb_ucfirst($tx['data']['pub_key']);
                    $transaction->coin = $tx['data']['coin'] ?? null;
                    $transaction->value = $tx['data']['value'] ?? 0;
                }

                if ($transaction->type === Transaction::TYPE_REDEEM_CHECK) {
                    $transaction->raw_check = $tx['data']['raw_check'] ?? null;
                    $transaction->proof = $tx['data']['proof'] ?? null;
                }

                if (
                    $transaction->type === Transaction::TYPE_SET_CANDIDATE_ONLINE ||
                    $transaction->type === Transaction::TYPE_SET_CANDIDATE_OFFLINE
                ) {
                    $pk = $tx['data']['pubkey'] ?? $tx['data']['pub_key'];
                    $transaction->pub_key = StringHelper::mb_ucfirst($pk);
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
     * Получить массив тэгов
     * @param array $data
     * @return array
     */
    public function decodeTxTagsFromApiData(array $data): array
    {
        $txs = $data['transactions'];
        $tagsData = [];
        foreach ($txs as $tx) {
            $tags = [];
            if (isset($tx['tx_result']) && isset($tx['tx_result']['tags'])) {
                $tags = $this->decodeTxTags($tx['tx_result']['tags']);
            }
            //TODO: возможно стоит добавить время, т.к. хэш может быть не уникальным
            if (\count($tags)) {
                $tagsData[$tx['hash']] = collect($tags);
            }
        }

        return $tagsData;
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

    /**
     * @param array $tagsData
     * @return TxTag[]
     */
    private function decodeTxTags(array $tagsData): array
    {
        return array_map(function ($el) {
            $tag = new TxTag();
            $tag->key = base64_decode($el['key']);
            $tag->value = '';
            try{
                $tag->value = base64_decode($el['value']);
            }catch (\Exception $exception){
                Log::channel('transactions')->error(
                    $exception->getFile() . ' ' .
                    $exception->getLine() . 'Tag decode: ' .
                    $exception->getMessage()
                );
            }
            return $tag;
        }, $tagsData);
    }

    private function getValueFromTxTag(array $tagsData): ?string
    {
        $tags = $this->decodeTxTags($tagsData);
        foreach ($tags as $tag) {
            if($tag->key === 'tx.return'){
                return $tag->value;
            }
        }
        return null;
    }
}