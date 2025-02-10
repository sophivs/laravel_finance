<?php

namespace App\Interfaces;

use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function createTransaction($fromAccountId, $toAccountId, $amount, $type): Transaction;
    public function reverseTransaction($transactionId): Transaction;
    public function findById($transactionId): Transaction|NUll;
}