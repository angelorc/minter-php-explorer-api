<?php

namespace App\Services;


use Illuminate\Support\Collection;

interface TransactionServiceInterface
{
    /**
     * Получить колекцию транзакций из данных API
     * @param array $data
     * @return Collection
     */
    public function decodeTransactionsFromApiData(array $data): Collection;

    /**
     * Получить колекцию тэгов транзакций из данных API
     * @param array $data
     * @return array
     */
    public function decodeTxTagsFromApiData(array $data): array;

    /**
     * Количество транзакций
     * @param string $address
     * @return int
     */
    public function getTotalTransactionsCount(string $address = null): int;

    /**
     * Количество транзакций за последние 24 часа
     * @return int
     */
    public function get24hTransactionsCount(): int;

    /**
     * Скорость обработки транзакций
     * @return float
     */
    public function getTransactionsSpeed(): float;

    /**
     * Получить сумму комиссии за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getCommission(\DateTime $startTime = null): string;

    /**
     * Получить среднюю комиссиию за транзакции с даты
     * @param \DateTime $startTime
     * @return string
     */
    public function getAverageCommission(\DateTime $startTime = null): string;


    /**
     * Данные по трнзакциям за 24 часа
     * @return array
     */
    public function get24hTransactionsData(): array;

    /**
     * Сохранить тэги транзакций
     * @param array $txTags
     */
    public function saveTransactionsTags(array $txTags): void;
}