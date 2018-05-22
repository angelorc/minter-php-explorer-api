<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\StatusServiceInterface;

class AddressController extends Controller
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
     *     path="/api/v1/address/{address}",
     *     tags={"Address"},
     *     summary="Данные аккаунта",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="path", name="address", type="string", description="Адрес", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="bipBalace",      type="integer", example="10"),
     *             @SWG\Property(property="bipBalanceUsd",  type="integer", example="1244"),
     *             @SWG\Property(property="txCount",        type="integer", example="40")
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
    public function address($address): array
    {
        //TODO: поменять значения, как станет ясно откуда брать
        return [
            'bipBalace' => 0,
            'bipBalanceUsd' => 0,
            'txCount' => 0,
        ];
    }

}
