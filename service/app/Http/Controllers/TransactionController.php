<?php

namespace App\Http\Controllers;

use App\Helpers\NodeExceptionHelper;
use App\Http\Resources\TransactionResource;
use App\Repository\TransactionRepositoryInterface;
use App\Services\MinterApiService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    use NodeTrait;

    public const BLOCKS_PER_PAGE = 50;

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    /** @var MinterApiService */
    protected $minterApiService;

    /**
     * Create a new controller instance.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->minterApiService = new MinterApiService($this->getActualNode());
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Список транзакций",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="query", name="block", type="integer", description="Высота блока"),
     *     @SWG\Parameter(in="query", name="address", type="string", description="Адрес"),
     *     @SWG\Parameter(in="query", name="addresses", type="string", description="Список адресов  (addresses[]=Mx...&addresses[]=Mx...)"),
     *     @SWG\Parameter(in="query", name="hash", type="string", description="Хэш"),
     *     @SWG\Parameter(in="query", name="hashes", type="string", description="Список хэшей (hashes[]=Mt...&hashes[]=Mt...)"),
     *     @SWG\Parameter(in="query", name="pubKey", type="string", description="Публичный ключ"),
     *     @SWG\Parameter(in="query", name="pubKeys", type="string", description="Список публичных ключей (pubKeys[]=Mh...&pubKeys[]=Mh...)"),
     *     @SWG\Parameter(in="query", name="page", type="integer", description="Номер страницы"),
     *     @SWG\Parameter(in="query", name="perPage", type="integer", description="Количество на странице"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="array",
     *                @SWG\Items(ref="#/definitions/Transaction")
     *             ),
     *             @SWG\Property(property="links", ref="#/definitions/TransactionLinksData"),
     *             @SWG\Property(property="meta", ref="#/definitions/TransactionMetaData")
     *         )
     *     )
     * )
     *
     * Get transactions list
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getList(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filter = [
            'block' => $request->get('block'),
            'address' => $request->get('address'),
            'addresses' => $request->get('addresses'),
            'hash' => $request->get('hash'),
            'hashes' => $request->get('hashes'),
            'pubKey' => $request->get('pubKey'),
            'pubKeys' => $request->get('pubKeys'),
        ];

        $perPage = $request->get('perPage', null) ?? $this::BLOCKS_PER_PAGE;

        $query = $this->transactionRepository->getAllQuery($filter);

        return TransactionResource::collection($query->orderByDesc('created_at')->paginate($perPage));

    }

    /**
     * @SWG\Get(
     *     path="/api/v1/transaction/{hash}",
     *     tags={"Transactions"},
     *     summary="Найти транзакцию по hash-сумме",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="path", name="hash", type="string", description="Hash-сумма транзакции", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data", ref="#/definitions/Transaction")
     *         )
     *     )
     * )
     *
     * Find transaction by hash
     *
     * @param string $hash
     * @return array| Response
     */
    public function getTransactionByHash(string $hash)
    {
        $transaction = $this->transactionRepository->findByHash($hash);

        if ($transaction) {
            return ['data' => new TransactionResource($transaction)];
        }

        return new Response([
            'error' => 'Transaction not found',
            'code' => 404
        ], 404);
    }

    /**
     * @SWG\Get(
     *     path="/api/v1/transaction/get-count/{address}",
     *     tags={"Transactions"},
     *     summary="Get transactions count by address",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(in="path", name="address", type="string", description="Account address", required=true),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="object",
     *               @SWG\Property(property="count",   type="integer", example="60"),
     *             )
     *         )
     *     )
     * )
     **/
    /**
     * @param string $address
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCountByAddress(string $address): array
    {
        return [
            'data' => $this->minterApiService->getTransactionsCountByAddress($address)
        ];
    }

    /**
     * @SWG\Post(
     *     path="/api/v1/transaction/push",
     *     tags={"Transactions"},
     *     summary="Push transaction to blockchain",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *      in="body",
     *      name="body", type="string",
     *      description="Transaction hash",
     *      required=true,
     *
     *      @SWG\Schema(
     *           @SWG\Property(property="transaction", type="string", example="fb6d7c4f53e389b0d02b81b9b69023d784fa660f..."),
     *      )
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="object",
     *               @SWG\Property(property="hash",   type="string", example="Mtfb6d7c4f53e389b0d02b81b9b69023d784fa660f"),
     *             )
     *         )
     *     ),
     *
     *     @SWG\Response(
     *         response=400,
     *         description="Error",
     *         @SWG\Schema(
     *             @SWG\Property(property="error",    type="object",
     *                @SWG\Property(property="code",   type="integer", example="60"),
     *                @SWG\Property(property="log",   type="string", example="Check tx error: Unexpected nonce. Expected: 1, got 17."),
     *             )
     *         )
     *     )
     * )
     **/
    /**
     * @param Request $request
     * @return Response
     */
    public function pushTransactionToBlockChain(Request $request): Response
    {
        $transaction = $request->get('transaction', null);
        $attempt = 0;
        $result = ['error' => 'Unknown error'];
        while ($attempt <= 5) {
            $result = $this->sendTransaction($transaction);
            if (isset($result['data'])) {
                return new Response($result, 200);
            }
            $attempt++;
        }
        return new Response($result, 400);
    }

    private function sendTransaction(string $transaction): array
    {
        try {
            $result = $this->minterApiService->pushTransactionToBlockChain($transaction);
        } catch (BadResponseException $e) {
            return ['error' => NodeExceptionHelper::handleNodeException($e)];
        } catch (GuzzleException $e) {
            return ['error' => NodeExceptionHelper::handleGuzzleException($e)];
        }
        return ['data' => $result];
    }
}
