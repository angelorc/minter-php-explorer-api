<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public const BLOCKS_PER_PAGE = 50;

    /** @var TransactionRepositoryInterface  */
    protected $transactionRepository;

    /**
     * Create a new controller instance.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
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
     *     @SWG\Parameter(in="query", name="page", type="integer", description="Номер страницы"),
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
     * Получить список транзакций
     *
     * @param Request $request
     * @return TransactionCollection
     */
    public function getList(Request $request): TransactionCollection
    {
        $filter = [
            'block' =>  $request->get('block'),
            'address' =>  $request->get('address'),
            'addresses' =>  $request->get('addresses'),
            'hash' => $request->get('hash'),
            'hashes' => $request->get('hashes'),
        ];

        $query = $this->transactionRepository->getAllQuery($filter);

        return new TransactionCollection($query->orderByDesc('created_at')->paginate($this::BLOCKS_PER_PAGE));

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
     * Получить информацию по транзакции по хэш-сумме
     *
     * @param string $hash
     * @return TransactionResource
     */
    public function getTransactionByHash(string $hash): TransactionResource
    {
        $transaction = $this->transactionRepository->findByHash($hash);

        return new TransactionResource($transaction);
    }
}
