<?php

namespace App\Http\Controllers;


use App\Http\Resources\CoinsResource;
use App\Repository\CoinsRepositoryInterface;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    /** @var CoinsRepositoryInterface */
    protected  $coinsRepository;

    /**
     * CoinController constructor.
     * @param CoinsRepositoryInterface $coinsRepository
     */
    public function __construct(CoinsRepositoryInterface $coinsRepository)
    {
        $this->coinsRepository = $coinsRepository;
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/coins",
     *     tags={"Coins"},
     *     summary="List of coins",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="symbol", type="string", description="Part of coin's symbol"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/Coin")
     *             )
     *         ),
     *     )
     * )
     *
     * @param Request $request
     * @return array
     */
    public function getList(Request $request): array
    {
        $filters['symbol'] = $request->get('symbol');

        return [
            'data' => CoinsResource::collection($this->coinsRepository->getList($filters))
        ];
    }
}