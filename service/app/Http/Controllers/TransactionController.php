<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Repository\TransactionRepositoryInterface;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
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
     *     @SWG\Parameter(in="query", name="block_height", type="integer", description="Высота блока"),
     *     @SWG\Parameter(in="query", name="page", type="integer", description="Номер страницы"),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="data",    type="array",
     *                @SWG\Items(ref="#/definitions/Transaction")
     *             )
     *         )
     *     )
     * )
     *
     * Получить список транзакций
     *
     * @param Request $request
     * @return array
     */
    public function getList(Request $request): array
    {
        $page = $request->get('page', 1);

        $filter = [
            'block_height' =>  $request->get('block_height'),
            'account' =>  $request->get('account'),
        ];

        $result = [];

        foreach ($this->transactionRepository->getAll($page, $filter) as $transaction) {
            $result[] = $this->prepareTransactionForResponse($transaction);
        }

        return [
            'totalCount' => \count($result),
            'data' => $result,
        ];

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
