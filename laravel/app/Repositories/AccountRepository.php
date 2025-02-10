<?php

namespace App\Repositories;

use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function getAll(): Collection
    {
        return Account::all();
    }

    public function findById($id): Account
    {
        return Account::find($id);
    }

    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);
        return $account;
    }

    public function delete(Account $account): void
    {
        $account->delete();
    }
}
