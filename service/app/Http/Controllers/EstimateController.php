<?php

namespace App\Http\Controllers;


use App\Helpers\NodeExceptionHelper;
use App\Services\MinterApiService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EstimateController
{
    use NodeTrait;

    /** @var MinterApiService */
    protected $minterApiService;

    /**
     * EstimateController constructor.
     */
    public function __construct()
    {
        $this->minterApiService = new MinterApiService($this->getActualNode());
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/estimate/coin-sell",
     *     tags={"Estimate"},
     *     summary="Return estimate of sell coin transaction",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="coinToSell",  type="string", description="Coin to sell", required=true),
     *     @SWG\Parameter(in="query", name="coinToBuy",   type="string", description="Coin to buy", required=true),
     *     @SWG\Parameter(in="query", name="valueToSell", type="integer", description="Value to sell", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",         type="object",
     *               @SWG\Property(property="will_get",   type="string", example="13682"),
     *               @SWG\Property(property="commission", type="string", example="100000000000000000"),
     *             )
     *         )
     *     )
     * )
     **/
    /**
     * @param Request $request
     * @return Response
     */
    public function sellCoin(Request $request): Response
    {
        try {
            $coinToSell = $request->get('coinToSell', null);
            $coinToBuy = $request->get('coinToBuy', null);
            $valueToSell = $request->get('valueToSell', null);
            $result = $this->minterApiService->estimateSellCoin($coinToSell, $coinToBuy, $valueToSell);
        } catch (BadResponseException $e) {
            return new Response(['error' => NodeExceptionHelper::handleNodeException($e)], 400);
        } catch (GuzzleException $e) {
            return new Response(['error' => NodeExceptionHelper::handleGuzzleException($e)], 400);
        }

        return new Response(['data' => $result], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/estimate/coin-buy",
     *     tags={"Estimate"},
     *     summary="Return estimate of buy coin transaction",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="coinToSell", type="string", description="Coin to sell", required=true),
     *     @SWG\Parameter(in="query", name="coinToBuy", type="string", description="Coin to buy", required=true),
     *     @SWG\Parameter(in="query", name="valueToBuy", type="integer", description="Value to buy", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",         type="object",
     *               @SWG\Property(property="will_pay",   type="string", example="13682"),
     *               @SWG\Property(property="commission", type="string", example="100000000000000000"),
     *             )
     *         )
     *     )
     * )
     **/
    /**
     * @param Request $request
     * @return Response
     */
    public function buyCoin(Request $request): Response
    {
        try {
            $coinToSell = $request->get('coinToSell', null);
            $coinToBuy = $request->get('coinToBuy', null);
            $valueToBuy = $request->get('valueToBuy', null);
            $result = $this->minterApiService->estimateBuyCoin($coinToSell, $coinToBuy, $valueToBuy);
            return new Response(['data' => $result], 200);
        } catch (BadResponseException $e) {
            return new Response(['error' => NodeExceptionHelper::handleNodeException($e)], 400);
        } catch (GuzzleException $e) {
            return new Response(['error' => NodeExceptionHelper::handleGuzzleException($e)], 400);
        }
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/estimate/tx-commission",
     *     tags={"Estimate"},
     *     summary="Get transaction commission",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="transaction", type="string", description="Transaction hash", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="object",
     *               @SWG\Property(property="commission",   type="string", example="100000000000000000"),
     *             )
     *         )
     *     )
     * )
     **/
    /**
     * @param Request $request
     * @return Response
     */
    public function txCommission(Request $request): Response
    {
        try {
            $transaction = $request->get('transaction', null);
            $result = $this->minterApiService->estimateTxCommission($transaction);
            return new Response(['data' => $result], 200);
        } catch (BadResponseException $e) {
            return new Response(['error' => NodeExceptionHelper::handleNodeException($e)], 400);
        } catch (GuzzleException $e) {
            return new Response(['error' => NodeExceptionHelper::handleGuzzleException($e)], 400);
        }
    }
}