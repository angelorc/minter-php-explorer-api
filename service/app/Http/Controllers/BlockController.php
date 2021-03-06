<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Repository\BlockRepositoryInterface;
use App\Services\StatusServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockController extends Controller
{
    public const BLOCKS_PER_PAGE = 50;

    /** @var BlockRepositoryInterface  */
    private $blockRepository;

    /** @var StatusServiceInterface  */
    private $statusService;

    /**
     * Create a new controller instance.
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param StatusServiceInterface $statusService
     */
    public function __construct(BlockRepositoryInterface $blockRepository, StatusServiceInterface $statusService)
    {
        $this->blockRepository = $blockRepository;
        $this->statusService = $statusService;
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/blocks",
     *     tags={"Blocks"},
     *     summary="Список блоков",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="page", type="integer", description="Номер страницы"),
     *     @SWG\Parameter(in="query", name="validator_id", type="integer", description="ID Валидатора"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/Block")
     *             ),
     *             @SWG\Property(property="links", ref="#/definitions/BlockLinksData"),
     *             @SWG\Property(property="meta", ref="#/definitions/BlockMetaData")
     *         ),
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getList(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Block::with('validators');

        return BlockResource::collection($query->orderByDesc('height')->paginate($this::BLOCKS_PER_PAGE));
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/block/{height}",
     *     tags={"Blocks"},
     *     summary="Найти блок по высоте",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="path", name="height", type="integer", description="Высота блока", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", ref="#/definitions/Block")
     *         )
     *     )
     * )
     *
     * @param string $height
     * @return BlockResource|Response
     */
    public function getBlockByHeight(string $height)
    {
        $block = $this->blockRepository->findByHeight($height);

        if ($block) {
            return new BlockResource($block, $this->statusService->getLastBlockHeight());
        }

        return new Response([
            'error' => 'Block not found',
            'code' => 404
        ], 404);
    }
}
