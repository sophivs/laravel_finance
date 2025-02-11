<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @OA\Get(
     *     path="/api/accounts",
     *     summary="List all accounts",
     *     tags={"Accounts"},
     *     security={
     *         {"sanctum": {}},
     *     },
     *     @OA\Response(response=200, description="List of accounts retrieved successfully"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json($this->accountService->getAllAccounts());
    }

    /**
     * @OA\Get(
     *     path="/api/accounts/{id}",
     *     summary="Get account details by ID",
     *     tags={"Accounts"},
     *     security={
     *         {"sanctum": {}},
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Account details retrieved successfully"),
     *     @OA\Response(response=404, description="Account not found")
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $account = $this->accountService->getAccountById($id);
            return response()->json($account);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Conta não encontrada'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/accounts",
     *     summary="Create a new account",
     *     tags={"Accounts"},
     *     security={
     *         {"sanctum": {}},
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "balance"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="balance", type="number", format="float", example=100.50)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Account created successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'balance' => 'required|numeric',
        ]);

        return response()->json($this->accountService->createAccount($validated), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/accounts/{id}",
     *     summary="Update an existing account",
     *     tags={"Accounts"},
     *     security={
     *         {"sanctum": {}},
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"balance"},
     *             @OA\Property(property="balance", type="number", format="float", example=200.75)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Account updated successfully"),
     *     @OA\Response(response=404, description="Account not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'balance' => 'nullable|numeric',
        ]);

        return response()->json($this->accountService->updateAccount($id, $validated));
    }

    /**
     * @OA\Delete(
     *     path="/api/accounts/{id}",
     *     summary="Delete an account",
     *     tags={"Accounts"},
     *     security={
     *         {"sanctum": {}},
     *     },
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Account deleted successfully"),
     *     @OA\Response(response=404, description="Account not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $this->accountService->deleteAccount($id);

        return response()->json('', Response::HTTP_NO_CONTENT);
    }
}
