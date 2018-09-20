<?php

namespace App\Http\Controllers;


use App\Http\Resources\RewardResource;
use App\Http\Resources\SlashResource;
use App\Repository\RewardsRepositoryInterface;
use App\Repository\SlashesRepositoryInterface;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected const EVENTS_PER_PAGE = 50;

    /** @var RewardsRepositoryInterface */
    protected $rewardsRepository;
    /** @var SlashesRepositoryInterface */
    protected $slashesRepository;

    /**
     * CoinController constructor.
     * @param RewardsRepositoryInterface $rewardsRepository
     */
    public function __construct(RewardsRepositoryInterface $rewardsRepository, SlashesRepositoryInterface $slashesRepository)
    {
        $this->rewardsRepository = $rewardsRepository;
        $this->slashesRepository = $slashesRepository;
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/events/rewards",
     *     tags={"Events"},
     *     summary="List of rewards",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="address", type="string", description="Address"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/Reward")
     *             ),
     *             @SWG\Property(property="links", ref="#/definitions/BlockLinksData"),
     *             @SWG\Property(property="meta", ref="#/definitions/BlockMetaData")
     *         ),
     *     )
     * )
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getRewardsList(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filters = [];

        if ($request->get('address', false)) {
            $filters[] = [
                'field' => 'address',
                'value' => $request->get('address'),
                'sign' => 'ilike',
            ];
        }

        $data = $this->rewardsRepository
            ->query($filters)
            ->with('block')
            ->orderByDesc('block_height')
            ->paginate($this::EVENTS_PER_PAGE);

        return RewardResource::collection($data);
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/events/slashes",
     *     tags={"Events"},
     *     summary="List of slashes",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="address", type="string", description="Address"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/Slash")
     *             ),
     *             @SWG\Property(property="links", ref="#/definitions/BlockLinksData"),
     *             @SWG\Property(property="meta", ref="#/definitions/BlockMetaData")
     *         ),
     *     )
     * )
     */
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getSlashesList(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filters = [];

        if ($request->get('address', false)) {
            $filters[] = [
                'field' => 'address',
                'value' => $request->get('address'),
                'sign' => 'ilike',
            ];
        }

        $data = $this->slashesRepository
            ->query($filters)
            ->with('block')
            ->orderByDesc('block_height')
            ->paginate($this::EVENTS_PER_PAGE);

        return SlashResource::collection($data);
    }
}