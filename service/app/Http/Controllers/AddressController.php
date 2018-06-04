<?php

namespace App\Http\Controllers;

use App\Services\TransactionServiceInterface;

class AddressController extends Controller
{
    private $transactionService;

    /**
     * Create a new controller instance.
     *
     * @param TransactionServiceInterface $transactionService
     */
    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

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
     * @param string $address
     * @return array
     */
    public function address(string $address): array
    {
        //TODO: поменять значения, как станет ясно откуда брать
        return [
            'bipBalace' => 0,
            'bipBalanceUsd' => 0,
            'txCount' => $this->transactionService->getTotalTransactionsCount($address),
        ];
    }

}
