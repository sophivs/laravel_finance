<?php

namespace App\Interfaces;

use App\Models\Account;
use Illuminate\Support\Collection;

interface AccountRepositoryInterface
{
    public function getAll(): Collection;
    public function findById($id): Account|NULL;
    public function create(array $data): Account;
    public function update(Account $account, array $data): Account;
    public function delete(Account $account): void;
}