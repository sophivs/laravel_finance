<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="List all transactions with optional filters",
     *     tags={"Transactions"},
     *     security={{ "sanctum": {} }},
     *     @OA\Response(response=200, description="List of transactions"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function index(): JsonResponse
    {
        $transactions = $this->transactionService->getAllTransactions();

        return response()->json($transactions, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/transactions/deposit",
     *     summary="Depositar dinheiro na conta",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"account_id", "amount"},
     *             @OA\Property(property="account_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=100.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Depósito realizado com sucesso"),
     *     @OA\Response(response=400, description="Erro na requisição")
     * )
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01'
        ]);


        return response()->json(
            $this->transactionService->deposit($request->account_id, $request->amount)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/transactions/transfer",
     *     summary="Transferir dinheiro entre contas",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_account_id", "to_account_id", "amount"},
     *             @OA\Property(property="from_account_id", type="integer", example=1),
     *             @OA\Property(property="to_account_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", format="float", example=50.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transferência realizada com sucesso"),
     *     @OA\Response(response=400, description="Erro na requisição")
     * )
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|integer|exists:accounts,id',
            'to_account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        return response()->json(
            $this->transactionService->transfer($request->from_account_id, $request->to_account_id, $request->amount)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/transactions/reverse",
     *     summary="Reverter uma transação",
     *     tags={"Transactions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id"},
     *             @OA\Property(property="transaction_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transação revertida com sucesso"),
     *     @OA\Response(response=400, description="Erro na requisição")
     * )
     */
    public function reverse(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id'
        ]);

        return response()->json(
            $this->transactionService->reverseTransaction($request->transaction_id)
        );
    }
}