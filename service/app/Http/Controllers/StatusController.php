<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\StatusServiceInterface;

class StatusController extends Controller
{
    private $statusService;

    /**
     * Create a new controller instance.
     *
     * @param StatusServiceInterface $statusService
     */
    public function __construct(StatusServiceInterface $statusService)
    {
        $this->statusService = $statusService;
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
     *     @SWG\Property(property="transactionsPerSecond", type="integer", example="12345")
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
            'bipPriceUsd' => 0,
            'bipPriceBtc' => 0,
            'bipPriceChange' => 0,
            'marketCap' => 0,

            'latestBlockHeight' => $this->statusService->getLastBlockHeight(),
            'totalTransactions' => Transaction::count(),
            'transactionsPerSecond' => $this->statusService->getTransactionsPerSecond(),
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
     *     summary="Количество транзакций за сегодня",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="data",    type="array",
     *                @SWG\Items(ref="#/definitions/CountChartData")
     *             )
     *         )
     *     )
     * )
     *
     * @return array
     */
    public function txCountChartData(): array
    {
        $date = date('Y-m-d');

        $data = Transaction::whereDate('created_at', $date)->count();

        return [
            'date' => $date,
            'amount' => $data,
        ];
    }
}
