<?php

namespace App\Http\Controllers;

use App\Http\Resources\TxCountCollection;
use App\Models\Transaction;
use App\Models\TxPerDay;
use App\Services\BlockServiceInterface;
use App\Services\StatusServiceInterface;
use App\Services\TransactionServiceInterface;

class StatusController extends Controller
{
    /**
     * @var StatusServiceInterface
     */
    protected $statusService;
    /**
     * @var TransactionServiceInterface
     */
    protected $transactionService;

    /**
     * @var BlockServiceInterface
     */
    protected $blockService;

    /**
     * Create a new controller instance.
     *
     * @param StatusServiceInterface $statusService
     * @param TransactionServiceInterface $transactionService
     * @param BlockServiceInterface $blockService
     */
    public function __construct(
        StatusServiceInterface $statusService,
        TransactionServiceInterface $transactionService,
        BlockServiceInterface $blockService
    ) {
        $this->statusService = $statusService;
        $this->transactionService = $transactionService;
        $this->blockService = $blockService;
    }

    /**
     * @SWG\Definition(
     *     definition="Status",
     *     type="object",
     *
     *     @SWG\Property(property="bipPriceUsd",           type="float", example="123.23"),
     *     @SWG\Property(property="bipPriceBtc",           type="float", example="1.23456789"),
     *     @SWG\Property(property="bipPriceChange",        type="float", example="1.23456789"),
     *     @SWG\Property(property="marketCap",             type="float", example="123456789.003"),
     *     @SWG\Property(property="averageBlockTime",      type="float", example="1.23"),
     *     @SWG\Property(property="latestBlockHeight",     type="integer", example="123456"),
     *     @SWG\Property(property="totalTransactions",     type="integer", example="1234569875975"),
     *     @SWG\Property(property="transactionsPerSecond", type="float", example="0.2")
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/status",
     *     tags={"Info"},
     *     summary="Статус сети",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="message", type="string"),
     *             @SWG\Property(property="data",    ref="#/definitions/Status")
     *         )
     *     )
     * )
     *
     * @return array
     */
    /**
     * Статут сети
     * @return array
     */
    public function status(): array
    {
        //TODO: поменять значения, как станет ясно откуда брать
        return [
            'bipPriceUsd' => 0.00007453,
            'bipPriceBtc' => 0.00000001,
            'bipPriceChange' => 10,
            'marketCap' => 10000000000 * 0.00007453,
            'latestBlockHeight' => $this->statusService->getLastBlockHeight(),
            'totalTransactions' => Transaction::count(),
            'transactionsPerSecond' => $this->transactionService->getTransactionsSpeed(),
            'averageBlockTime' => $this->statusService->getAverageBlockTime(),
        ];
    }

    /**
     * @SWG\Definition(
     *     definition="CountChartData",
     *     type="object",
     *
     *     @SWG\Property(property="date",   type="string",  example="2018-05-14"),
     *     @SWG\Property(property="amount", type="integer", example="123456789")
     * )
     */

    /**
     * @SWG\Get(
     *     path="/api/v1/txCountChartData",
     *     tags={"Info"},
     *     summary="Количество транзакций по дням за последние 14",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/CountChartData")
     *             )
     *         )
     *     )
     * )
     *
     * @return TxCountCollection
     */
    public function txCountChartData(): TxCountCollection
    {
        return new TxCountCollection(TxPerDay::limit(14)->get());
    }


    /**
     * @SWG\Get(
     *     path="/api/v1/status_page",
     *     tags={"Info"},
     *     summary="Статус сети",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="message", type="string"),
     *             @SWG\Property(property="data",    ref="#/definitions/Status")
     *         )
     *     )
     * )
     *
     * @return array
     */
    public function statusPage(): array
    {
        return [
            'status' => $this->statusService->isActiveStatus() ? 'active' : 'down',
            'uptime' => $this->statusService->getUpTime(),
            'number_of_blocks' => $this->statusService->getLastBlockHeight(),
            'block_speed_24h' => $this->blockService->blockSpeed24h(),
            'tx_total_count' => $this->transactionService->getTotalTransactionsCount(),
            'tx_24h_count' => $this->transactionService->get24hTransactionsCount(),
            'tx_per_second' => $this->transactionService->getTransactionsSpeed(),
            'active_validators' => '???',
            'total_validators_count' => '???',
            'average_tx_commission' => '???',
            'total_commission' => '???',
        ];
    }
}
