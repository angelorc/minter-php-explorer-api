<?php

namespace App\Http\Controllers;


use App\Helpers\StringHelper;
use App\Http\Resources\EventChartCollection;
use App\Http\Resources\RewardResource;
use App\Http\Resources\SlashResource;
use App\Repository\EventsRepositoryInterface;
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
     * @param SlashesRepositoryInterface $slashesRepository
     */
    public function __construct(
        RewardsRepositoryInterface $rewardsRepository,
        SlashesRepositoryInterface $slashesRepository
    ) {
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
                'value' => StringHelper::mb_ucfirst($request->get('address')),
                'sign' => '=',
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

    /**
     *  * @SWG\Definition(
     *     definition="RewardChartData",
     *     type="object",
     *
     *     @SWG\Property(property="block",     type="float",     example="3484320.973646"),
     *     @SWG\Property(property="timestamp", type="timestamp", example="2018-05-18 15:06:10+00")
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/events/rewards/chart/{address}",
     *     tags={"Events"},
     *     summary="Rewards amount by minutes/hours/days",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="scale",     type="string", description="Time period: minute|hour|day (default)"),
     *     @SWG\Parameter(in="query", name="startTime", type="string", description="Start time. Formats: YYYY-MM-DD | YYYY-MM-DD HH:MM:SS| YYYY-MM-DD HH:MM:SS+ZZ"),
     *     @SWG\Parameter(in="query", name="endTime",   type="string", description="End time. Formats: YYYY-MM-DD | YYYY-MM-DD HH:MM:SS| YYYY-MM-DD HH:MM:SS+ZZ"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/RewardChartData")
     *             )
     *         ),
     *     )
     * )
     */
    /**
     * @param string $address
     * @param Request $request
     * @return EventChartCollection
     */
    public function getRewardsChartData(string $address, Request $request): EventChartCollection
    {
        return $this->getChartData($address, $request, $this->rewardsRepository);
    }

    /**
     * @param string $address
     * @param Request $request
     * @param EventsRepositoryInterface $repository
     * @return EventChartCollection
     */
    private function getChartData(
        string $address,
        Request $request,
        EventsRepositoryInterface $repository
    ): EventChartCollection {
        $scale = $request->get('scale', 'day');

        if (!\in_array($scale, ['minute', 'hour', 'day'])) {
            $scale = 'day';
        }

        if ($request->get('startTime', false)) {
            $startTime = new \DateTime($request->get('startTime', false));
        } else {
            $startTime = new \DateTime();
            $startTime->modify('-30 day');
        }

        if ($request->get('endTime', false)) {
            $endTime = new \DateTime($request->get('endTime', false));
        } else {
            $endTime = new \DateTime();
        }

        $startTime->setTimezone(new \DateTimeZone('UTC'));
        $endTime->setTimezone(new \DateTimeZone('UTC'));

        $result = $repository->getChartData($address, $scale, $startTime, $endTime);

        return new EventChartCollection(collect($result));
    }
}