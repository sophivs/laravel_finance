<?php

namespace App\Repositories;

use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Transaction::all();
    }

    public function createTransaction($fromAccountId, $toAccountId, $amount, $type): Transaction
    {
        return Transaction::create([
            'sender_id' => $fromAccountId,
            'receiver_id' => $toAccountId,
            'amount' => $amount,
            'transaction_type' => $type,
            'status' => 'completed',
            'reversed' => false
        ]);
    }

    public function reverseTransaction($transactionId): Transaction
    {
        return Transaction::where('id', $transactionId)->update([
            'status' => 'reversed',
            'reversed' => true
        ]);
    }

    public function findById($transactionId): ?Transaction
    {
        return Transaction::find($transactionId);
    }
}
