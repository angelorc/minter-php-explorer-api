<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Validator;
use App\Repository\BlockRepositoryInterface;
use App\Services\StatusServiceInterface;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /** @var BlockRepositoryInterface  */
    private $blockRepository;

    /** @var StatusServiceInterface  */
    private $statusService;

    /**
     * Create a new controller instance.
     *
     * @param BlockRepositoryInterface $blockRepository
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
     *     @SWG\Parameter(in="query", name="validator_id", type="integer", description="ID Валидатора"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="data",    type="array",
     *                @SWG\Items(ref="#/definitions/Block")
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return array
     */
    public function getList(Request $request): array
    {

        $filter = ['validator_id' =>  $request->get('validator_id')];

        $result = [];

        foreach ($this->blockRepository->getAll($filter) as $block) {
            $result[] = $this->prepareBlockForResponse($block);
        }

        return [
            'latestBlockHeight' => $this->statusService->getLastBlockHeight(),
            'data' => $result,
        ];
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
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="data",    ref="#/definitions/Block")
     *         )
     *     )
     * )
     *
     * @param string $height
     * @return array
     */
    public function getBlockByHeight(string $height): array
    {

        $block = $this->blockRepository->findByHeight($height);

        return $block ? $this->prepareBlockForResponse($block) : [];
    }

    /**
     * @param Block $block
     * @return array
     */
    private function prepareBlockForResponse(Block $block): array
    {
        $result = [
            'id' => $block->getKey(),
            'block' => $block->height,
            'timestamp' => $block->timestamp,
            'txCount' => $block->tx_count,
            'blockReward' => $block->block_reward,
            'validators' => []
        ];

        /** @var Validator $validator */
        foreach ($block->validators as $validator) {
            $result['validators'][] = [
                'id' => $validator->getKey(),
                'address' => $validator->address,
                'publicKey' => $validator->public_key,
                'name' => $validator->name,
            ];
        }

        return $result;
    }
}
