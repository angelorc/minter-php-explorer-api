<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Services\BalanceServiceInterface;
use App\Services\TransactionServiceInterface;
use Illuminate\Http\Request;
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

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addresses(Request $request): array
    {
        $this->validate($request, [
            'addresses' => 'required|array',
            'addresses.*' => 'string'
        ]);

        $result = [];

        foreach ($request->get('addresses') as $address){
            $data = $this->address($address);
            $data = $data['data'];
            $data['address'] = $address;
            $result[] = $data;
        }

        return [
            'data' => $result
        ];
    }

    /**
     * @SWG\Definition(
     *     definition="MultiAddressBalance",
     *     type="object",
     *
     *     @SWG\Property(property="address",    type="string", example="Mxa111f6245f3c497d19f1be4e4116ecea83721201"),
     *     @SWG\Property(property="bipTotal",   type="float",  example="402.87"),
     *     @SWG\Property(property="usdTotal",   type="float",  example="402.87"),
     *     @SWG\Property(property="balace",     type="array",  @SWG\Items(ref="#/definitions/Coins")),
     *     @SWG\Property(property="balanceUsd", type="array",  @SWG\Items(ref="#/definitions/Coins"))
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/address",
     *     tags={"Address"},
     *     summary="Данные по нескольким адресам",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="addresses", type="string", description="Массив адресов", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *              @SWG\Property(property="data", type="array",  @SWG\Items(ref="#/definitions/MultiAddressBalance")),
     *         )
     *     )
     * )
     *
     * @return array
     */

    /**
     * Посчитать суммарный баланс адреса
     * @param Collection $coins
     * @return float
     */
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
