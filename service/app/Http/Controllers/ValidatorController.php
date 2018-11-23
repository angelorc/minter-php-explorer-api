<?php

namespace App\Http\Controllers;


use App\Helpers\MathHelper;
use App\Repository\CoinsRepositoryInterface;
use App\Services\ValidatorServiceInterface;

class ValidatorController extends Controller
{
    /** @var CoinsRepositoryInterface */
    protected $validatorService;

    /**
     * CoinController constructor.
     * @param ValidatorServiceInterface $validatorService
     */
    public function __construct(ValidatorServiceInterface $validatorService)
    {
        $this->validatorService = $validatorService;
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/validator/{publicKey}",
     *     tags={"Validator"},
     *     summary="Validator info",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="path", name="Public Key", type="string", description="Validator public key"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="object",
     *               @SWG\Property(property="status",   type="integer", example="2"),
     *               @SWG\Property(property="count",   type="integer", example="23"),
     *               @SWG\Property(property="stake",   type="float", example="25.53556"),
     *               @SWG\Property(property="part",   type="float", example="0.53556"),
     *               @SWG\Property(property="delegator_list", type="array",
     *                 @SWG\Items(ref="#/definitions/Delegator")
     *               )
     *             )
     *         )
     *     )
     * )
     *
     * @param string $pk
     * @return array
     */
    public function info(string $pk): array
    {

        $validatorStake = $this->validatorService->getStake($pk);
        $totalStake = $this->validatorService->getTotalStake();
        $delegatorList = $this->validatorService->getDelegatorList($pk);

        return [
            'data' => [
                'status' => $this->validatorService->getStatus($pk),
                'stake' => MathHelper::makeAmountFromIntString($validatorStake['stake']),
                'part' => bcdiv($validatorStake['stake'], $totalStake, 5),
                'count' => $delegatorList->count(),
                'delegator_list' => $delegatorList
            ]
        ];
    }
}