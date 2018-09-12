<?php

namespace App\Http\Controllers;

use App\Models\BalanceChannel;
use App\Traits\NodeTrait;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use NodeTrait;

    /**
     * @var \phpcent\Client
     */
    private $centrifuge;

    /**
     * Create a new controller instance.
     *
     * @param \phpcent\Client $centrifuge
     */
    public function __construct(\phpcent\Client $centrifuge)
    {
        $this->centrifuge = $centrifuge;
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
     * @SWG\Definition(
     *     definition="wsConnectData",
     *     type="object",
     *
     *     @SWG\Property(property="timestamp", type="token",  example="1530715446"),
     *     @SWG\Property(property="token",     type="token",  example="c24b5d9f6e4d30ede103a310269c5e004d0e9194f0ec04b082fd687aca523b96")
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/settings/get-ws-data",
     *     tags={"Address"},
     *     summary="Get WebSocket connection data",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="user", type="string", description="ID пользователя (если есть)"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *              @SWG\Property(property="data", ref="#/definitions/wsConnectData")
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
     * @throws \Exception
     */
    public function getWsConnectData(Request $request): array
    {
        $user = $request->get('user', '');
        $timestamp = time();
        $token = $this->centrifuge->generateClientToken($user, $timestamp);
        return [
            'data' => [
                'timestamp' => $timestamp,
                'token' => $token,
            ]
        ];
    }
}
