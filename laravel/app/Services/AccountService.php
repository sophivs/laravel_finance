<?php

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;
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
}
