<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\AccountService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class TransactionService
{
    protected $transactionRepository;
    protected $accountService;

    public function __construct(TransactionRepository $transactionRepository, AccountService $accountService)
    {
        $this->transactionRepository = $transactionRepository;
        $this->accountService = $accountService;
    }

    public function getAllTransactions(): Collection
    {
        return $this->transactionRepository->getAll();
    }

    public function deposit($accountId, $amount): ?Transaction
    {
        DB::beginTransaction();
        try {
            // Atualiza saldo
            $this->accountService->increaseBalance($accountId, $amount);

            // Registra a transação
            $transaction = $this->transactionRepository->createTransaction($accountId, $accountId, $amount, 'deposit');

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages(['error' => 'Erro ao processar depósito: ' . $e->getMessage()]);
        }
    }

    public function transfer($fromAccountId, $toAccountId, $amount): ?Transaction
    {
        DB::beginTransaction();
        try {
            // Validação e transferência de saldo
            $this->accountService->transferBalance($fromAccountId, $toAccountId, $amount);

            // Registra a transação
            $transaction = $this->transactionRepository->createTransaction($fromAccountId, $toAccountId, $amount, 'transfer');

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages(['error' => 'Erro ao processar transferência: ' . $e->getMessage()]);
        }
    }

    public function reverseTransaction($transactionId): ?Transaction
    {
        DB::beginTransaction();
        try {
            $transaction = $this->transactionRepository->findById($transactionId);

            if (!$transaction || $transaction->reversed) {
                throw ValidationException::withMessages(['error' => 'Transação inválida ou já revertida.']);
            }

            // Chama AccountService para reverter a transação
            $this->accountService->reverseTransaction($transaction);

            // Marca a transação como revertida
            $transaction->reversed = TRUE;
            $transaction->status = 'reversed';
            $transaction->save();

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages(['error' => 'Erro ao reverter transação: ' . $e->getMessage()]);
        }
    }
}
