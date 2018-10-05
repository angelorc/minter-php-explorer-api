<?php

namespace App\Http\Controllers;

use App\Http\Resources\TxCountCollection;
use App\Models\MinterNode;
use App\Models\TxPerDay;
use App\Services\BlockServiceInterface;
use App\Services\MinterApiService;
use App\Services\StatusServiceInterface;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorServiceInterface;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class StatusController extends Controller
{
    use NodeTrait;

    /**
     * @var StatusServiceInterface
     */
    protected $statusService;

    /**
     * @var TransactionServiceInterface
     */
    protected $transactionService;

    /**
     * @var BlockServiceInterface
     */
    protected $blockService;

    /**
     * @var ValidatorServiceInterface
     */
    private $validatorService;

    /**
     * Create a new controller instance.
     *
     * @param StatusServiceInterface $statusService
     * @param TransactionServiceInterface $transactionService
     * @param BlockServiceInterface $blockService
     * @param ValidatorServiceInterface $validatorService
     */
    public function __construct(
        StatusServiceInterface $statusService,
        TransactionServiceInterface $transactionService,
        BlockServiceInterface $blockService,
        ValidatorServiceInterface $validatorService
    )
    {
        $this->statusService = $statusService;
        $this->transactionService = $transactionService;
        $this->blockService = $blockService;
        $this->validatorService = $validatorService;
    }

    /**
     * @SWG\Definition(
     *     definition="Status",
     *     type="object",
     *
     *     @SWG\Property(property="bipPriceUsd",           type="float", example="123.23"),
     *     @SWG\Property(property="bipPriceBtc",           type="float", example="1.23456789"),
     *     @SWG\Property(property="bipPriceChange",        type="float", example="1.23456789"),
     *     @SWG\Property(property="marketCap",             type="float", example="123456789.003"),
     *     @SWG\Property(property="averageBlockTime",      type="float", example="1.23"),
     *     @SWG\Property(property="latestBlockHeight",     type="integer", example="123456"),
     *     @SWG\Property(property="totalTransactions",     type="integer", example="1234569875975"),
     *     @SWG\Property(property="transactionsPerSecond", type="float", example="0.2")
     * )
     */
    /**
     * @SWG\Get(
     *     path="/api/v1/status",
     *     tags={"Info"},
     *     summary="Статус сети",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="message", type="string"),
     *             @SWG\Property(property="data",    ref="#/definitions/Status")
     *         )
     *     )
     * )
     *
     * @return array
     */

    /**
     * Network status info
     * @return array
     */
    public function status(): array
    {
        return $this->statusService->getStatusInfo();
    }

    /**
     * @SWG\Definition(
     *     definition="CountChartData",
     *     type="object",
     *
     *     @SWG\Property(property="date",   type="string",  example="2018-05-14"),
     *     @SWG\Property(property="amount", type="integer", example="123456789")
     * )
     */

    /**
     * @SWG\Get(
     *     path="/api/v1/tx-count-chart-data",
     *     tags={"Info"},
     *     summary="Количество транзакций по дням за последние 14",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", type="array",
     *                @SWG\Items(ref="#/definitions/CountChartData")
     *             )
     *         )
     *     )
     * )
     *
     * @return TxCountCollection
     */
    public function txCountChartData(): TxCountCollection
    {
        $txCount = Cache::get('txCountChartData', null);
        if (!$txCount) {
            $txCount = TxPerDay::limit(14)->orderBy('date', 'desc')->get();
            Cache::put('txCountChartData', $txCount, 60);
        }
        return new TxCountCollection($txCount);
    }

    /**
     * @SWG\Definition(
     *     definition="NetworkStatusData",
     *     type="object",
     *
     *     @SWG\Property(property="status",               type="string",  example="active"),
     *     @SWG\Property(property="uptime",               type="integer", example="99"),
     *     @SWG\Property(property="numberOfBlocks",       type="integer", example="9999"),
     *     @SWG\Property(property="blockSpeed24h",        type="float",   example="0.01458333"),
     *     @SWG\Property(property="txTotalCount",         type="int",     example="1458333"),
     *     @SWG\Property(property="tx24hCount",           type="int",     example="333"),
     *     @SWG\Property(property="txPerSecond",          type="float",   example="0.0145"),
     *     @SWG\Property(property="activeValidators",     type="int",     example="5"),
     *     @SWG\Property(property="totalValidatorsCount", type="int",     example="15"),
     *     @SWG\Property(property="averageTxCommission",  type="float",   example="0.0015"),
     *     @SWG\Property(property="totalCommission",      type="float",   example="19884.23")
     * )
     */

    /**
     * @SWG\Get(
     *     path="/api/v1/status-page",
     *     tags={"Info"},
     *     summary="Статус сети",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", ref="#/definitions/NetworkStatusData")
     *         )
     *     )
     * )
     *
     * @return array
     */
    public function statusPage(): array
    {
        $apiService = new MinterApiService($this->getActualNode());
        $transactionData = $this->transactionService->get24hTransactionsData();
        $activeValidators = $this->validatorService->getActiveValidatorsCount();

        $status = Cache::get('explorer_status', false);

        if (!$status) {
            $status = $this->statusService->isActiveStatus() ? 'active' : 'down';
        }

        try {
            $candidatesData = $apiService->getCandidatesData($this->blockService->getExplorerLastBlockHeight());
            $activeCandidates = 0;
            foreach ($candidatesData as $candidate) {
                if ($candidate['status'] === 2) {
                    $activeCandidates++;
                }
            }
        } catch (GuzzleException $e) {
            $activeCandidates = $this->validatorService->getActiveValidatorsCount();
        }

        return [
            'data' => [
                'status' => $status,
                'uptime' => $this->statusService->getUpTime(),
                'numberOfBlocks' => $this->statusService->getLastBlockHeight(),
                'blockSpeed24h' => $this->statusService->getAverageBlockTime(),
                'txTotalCount' => $this->transactionService->getTotalTransactionsCount(),
                'tx24hCount' => $transactionData['count'],
                'txPerSecond' => $transactionData['perSecond'],
                'activeValidators' => $activeValidators,
                'activeCandidates' => $activeCandidates,
                'averageTxCommission' => $transactionData['avg'],
                'totalCommission' => $transactionData['sum'],
            ]
        ];
    }

    /**
     * Get actual minter node
     * @return array
     */
    public function getActualNodeData(): array
    {
        /** @var MinterNode $node */
        $node = MinterNode::where('is_active', true)->where('is_local', false)->orderBy('ping', 'asc')->first();

        if ($node) {
            return [
                'data' => [
                    'protocol' => $node->is_secure ? 'https' : 'http',
                    'host' => $node->host,
                    'port' => $node->port,
                    'link' => $node->fullLink
                ]
            ];
        }

        return ['data' => []];
    }
}
