<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Services\BalanceServiceInterface;
use App\Services\TransactionServiceInterface;

class AddressController extends Controller
{
    /**
     * @var TransactionServiceInterface
     */
    private $transactionService;
    /**
     * @var BalanceServiceInterface
     */
    private $balanceService;

    /**
     * Create a new controller instance.
     *
     * @param TransactionServiceInterface $transactionService
     * @param BalanceServiceInterface $balanceService
     */
    public function __construct(
        TransactionServiceInterface $transactionService,
        BalanceServiceInterface $balanceService
    ) {
        $this->transactionService = $transactionService;
        $this->balanceService = $balanceService;
    }


    /**
     * @SWG\Definition(
     *     definition="Coins",
     *     type="object",
     *
     *     @SWG\Property(property="coin",   type="float",  example="12.987"),
     * )
     */

    /**
     * @SWG\Definition(
     *     definition="AddressBalance",
     *     type="object",
     *
     *     @SWG\Property(property="bipBalace", type="array",  @SWG\Items(ref="#/definitions/Coins")),
     *     @SWG\Property(property="bipBalanceUsd", type="array", @SWG\Items(ref="#/definitions/Coins")),
     *     @SWG\Property(property="txCount",       type="integer", example="40")
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
     *              @SWG\Property(property="data", ref="#/definitions/AddressBalance")
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
        $balance = $this->balanceService->getAddressBalance($address);

        $pipBalance = $balance->map(function ($item) {
            /** @var Coin $item */
            return [$item->getName() => $item->getAmount()];
        });

        $bipBalanceUsd = $balance->map(function ($item) {
            /** @var Coin $item */
            return [$item->getName() => $item->getUsdAmount()];
        });

        return [
            'data' => [
                'bipBalace' => $pipBalance,
                'bipBalanceUsd' => $bipBalanceUsd,
                'txCount' => $this->transactionService->getTotalTransactionsCount($address),
            ]
        ];
    }
}
