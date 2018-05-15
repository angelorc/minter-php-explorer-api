<?php

namespace App\Http\Controllers;

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
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Success",
     *         @SWG\Schema(
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
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
     *             @SWG\Property(property="success", type="boolean"),
     *             @SWG\Property(property="code",    type="integer"),
     *             @SWG\Property(property="data",    ref="#/definitions/Transaction")
     *         )
     *     )
     * )
     *
     * Получить информацию по транзакции по хэш-сумме
     *
     * @param string $hash
     * @return array
     */
    public function getTransactionByHash(string $hash): array
    {
        $transaction = $this->transactionRepository->findByHash($hash);

        return $transaction ? $this->prepareTransactionForResponse($transaction) : [];
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    private function prepareTransactionForResponse(Transaction $transaction): array
    {
        $result = [
            'hash' => $transaction->hash,
            'status' => 'success', //TODO: пока в тз так. изменить
            'nonce' => $transaction->nonce,
            'block' => $transaction->block->height,
            'timestamp' => $transaction->block->timestamp,
            'fee' => $transaction->fee,
            'type' => $transaction->type,
            'data' => [
                'from' => $transaction->from,
                'to' => $transaction->to,
                'coin' => $transaction->coin,
                'amount' => $transaction->value,
            ]
        ];

        return $result;
    }
}
