<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Services\BalanceServiceInterface;
use App\Services\TransactionServiceInterface;
use Illuminate\Support\Collection;

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
     *     @SWG\Property(property="balace",     type="array",  @SWG\Items(ref="#/definitions/Coins")),
     *     @SWG\Property(property="balanceUsd", type="array",  @SWG\Items(ref="#/definitions/Coins")),
     *     @SWG\Property(property="txCount",    type="integer", example="40"),
     *     @SWG\Property(property="bipTotal",   type="float", example="402.87"),
     *     @SWG\Property(property="usdTotal",   type="float", example="402.87")
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

        $bipBalance = $balance->map(function ($item) {
            /** @var Coin $item */
            return [$item->getName() => $item->getAmount()];
        });

        $bipBalanceUsd = $balance->map(function ($item) {
            /** @var Coin $item */
            return [$item->getName() => $item->getUsdAmount()];
        });

        return [
            'data' => [
                'balace' => $bipBalance,
                'balanceUsd' => $bipBalanceUsd,
                'bipTotal' => $this->getTotalBalance($bipBalance),
                'usdTotal' => $this->getTotalBalance($bipBalanceUsd),
                'txCount' => $this->transactionService->getTotalTransactionsCount($address),
            ]
        ];
    }

    private function getTotalBalance(Collection $coins): float
    {
        return $coins->reduce(function ($sum, $item) {
            foreach ($item as $amount) {
                $sum += $amount;
            }
            return $sum;
        }, 0);
    }
}
