<?php

namespace App\Http\Controllers;

use App\Models\BalanceChannel;
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
     * @var \phpcent\Client
     */
    private $centrifuge;

    /**
     * Create a new controller instance.
     *
     * @param TransactionServiceInterface $transactionService
     * @param BalanceServiceInterface $balanceService
     * @param \phpcent\Client $centrifuge
     */
    public function __construct(
        TransactionServiceInterface $transactionService,
        BalanceServiceInterface $balanceService,
        \phpcent\Client $centrifuge
    ) {
        $this->transactionService = $transactionService;
        $this->balanceService = $balanceService;
        $this->centrifuge = $centrifuge;
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
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addresses(Request $request): array
    {
        $this->validate($request, [
            'addresses' => 'required|array',
            'addresses.*' => 'string|size:42'
        ]);

        $result = [];

        foreach ($request->get('addresses') as $address) {
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
     * Статут сети
     * @param string $address
     * @return array
     */
    public function address(string $address): array
    {
        return [
            'data' => [
                'txCount' => $this->transactionService->getTotalTransactionsCount($address),
                'coins' => $this->balanceService->getAddressBalance($address) ?? []
            ]
        ];
    }

    /**
     * @SWG\Definition(
     *     definition="AddressBalanceChannel",
     *     type="object",
     *
     *     @SWG\Property(property="channel",   type="string", example="DcxNTQ0NjtjMjRiNWQ5Z"),
     *     @SWG\Property(property="timestamp", type="token",  example="1530715446"),
     *     @SWG\Property(property="token",     type="token",  example="c24b5d9f6e4d30ede103a310269c5e004d0e9194f0ec04b082fd687aca523b96")
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/address/get-balance-channel",
     *     tags={"Address"},
     *     summary="Get WebSocket connection data",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="addresses[]", type="string", description="Массив адресов", required=true),
     *     @SWG\Parameter(in="query", name="user", type="string", description="ID пользователя (если есть)"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *              @SWG\Property(property="data", ref="#/definitions/AddressBalanceChannel")
     *         )
     *     )
     * )
     *
     * @return array
     */
    /**
     * Get WebSocket connection data for balance listening
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function getBalanceWsChannel(Request $request): array
    {
        $this->validate($request, [
            'addresses' => 'required|array',
            'addresses.*' => 'string|size:42'
        ]);

        $addresses = $request->get('addresses', []);

        $user = $request->get('user', '');

        $timestamp = time();

        $token = $this->centrifuge->generateClientToken($user, $timestamp);

        $channel = substr(base64_encode(implode(';', [$timestamp, $token, random_bytes(5)])), random_int(0, 10), 20);

        foreach ($addresses as $address) {
            BalanceChannel::create([
                'name' => $channel,
                'address' => mb_strtolower($address)
            ]);
        }

        return [
            'data' => [
                'channel' => $channel,
                'timestamp' => $timestamp,
                'token' => $token,
            ]
        ];
    }

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
