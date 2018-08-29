<?php

namespace App\Http\Controllers;

use App\Http\Resources\TxCountCollection;
use App\Models\TxPerDay;
use App\Services\BlockServiceInterface;
use App\Services\StatusServiceInterface;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorServiceInterface;
use Illuminate\Support\Facades\Cache;

class StatusController extends Controller
{
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
    ) {
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
     * Статут сети
     * @return array
     */
    public function status(): array
    {
        //TODO: поменять значения, как станет ясно откуда брать
        return [
            'bipPriceUsd' => 0.00007453,
            'bipPriceBtc' => 0.00000001,
            'bipPriceChange' => 10,
            'marketCap' => 10000000000 * 0.00007453,
            'latestBlockHeight' => $this->statusService->getLastBlockHeight(),
            'latestBlockTime' => Cache::get('last_block_time', null),
            'totalTransactions' => $this->transactionService->getTotalTransactionsCount(),
            'transactionsPerSecond' => $this->transactionService->getTransactionsSpeed(),
            'averageBlockTime' => $this->statusService->getAverageBlockTime(),
        ];
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
        return new TxCountCollection(TxPerDay::limit(14)->orderBy('date', 'desc')->get());
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
        $transactionData = $this->transactionService->get24hTransactionsData();

        $status = Cache::get('explorer_status', false);

        if(!$status){
            $status = $this->statusService->isActiveStatus() ? 'active' : 'down';
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
                'activeValidators' => $this->validatorService->getActiveValidatorsCount(),
                'totalValidatorsCount' => $this->validatorService->getTotalValidatorsCount(),
                'averageTxCommission' => $transactionData['avg'],
                'totalCommission' => $transactionData['sum'],
            ]
        ];
    }
}
