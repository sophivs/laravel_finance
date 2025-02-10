<?php

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class AccountService
{
    protected $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function getAllAccounts(): Collection
    {
        return $this->accountRepository->getAll();
    }

    public function getAccountById($id): ?Account
    {
        $account = $this->accountRepository->findById($id);

        if (!$account) {
            throw ValidationException::withMessages(['error' => 'Conta não encontrada']);
        }

        return $account;
    }

    public function createAccount(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    public function updateAccount($id, array $data): Account
    {
        $account = $this->getAccountById($id);
        return $this->accountRepository->update($account, $data);
    }

    public function deleteAccount($id): void
    {
        $account = $this->getAccountById($id);
        $this->accountRepository->delete($account);
    }

    public function increaseBalance($accountId, float $amount): void
    {
        $account = $this->getAccountById($accountId);
        $account->balance += $amount;
        $account->save();
    }

    public function decreaseBalance($accountId, float $amount, $checkAmount = TRUE): void
    {
        $account = $this->getAccountById($accountId);

        if ($checkAmount and $account->balance < $amount) {
            throw new Exception("Saldo insuficiente.");
        }

        $account->balance -= $amount;
        $account->save();
    }

    public function transferBalance($fromAccountId, $toAccountId, float $amount): void
    {
        DB::beginTransaction();
        try {
            $this->decreaseBalance($fromAccountId, $amount);
            $this->increaseBalance($toAccountId, $amount);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Erro ao transferir saldo: " . $e->getMessage());
        }
    }

    public function reverseTransaction(Transaction $transaction)
    {
        if ($transaction->reversed) {
            throw new Exception("Transação já foi revertida.");
        }

        if ($transaction->transaction_type === 'transfer') {
            $this->increaseBalance($transaction->sender_id, $transaction->amount);
            $this->decreaseBalance($transaction->receiver_id, $transaction->amount, FALSE);
        } elseif ($transaction->transaction_type === 'deposit') {
            $this->decreaseBalance($transaction->receiver_id, $transaction->amount, FALSE);
        }
    }
}
