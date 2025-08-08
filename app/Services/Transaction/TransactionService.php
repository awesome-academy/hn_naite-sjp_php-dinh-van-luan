<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\RecurringTransaction;

class TransactionService
{
    public function createTransaction(array $data, User $user): Transaction
    {
        return DB::transaction(function () use ($data, $user) {
            // Lock row wallet to avoid race condition
            $wallet = Wallet::where('id', $data['wallet_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

            if ($wallet->user_id !== $user->id) {
                throw new \RuntimeException(__('transaction.unauthorized_access'));
            }

            if ($wallet->balance < $data['amount']) {
                throw new \RuntimeException(__('transaction.insufficient_balance'));
            }

            $wallet->balance -= $data['amount'];
            $wallet->save();

            $data['user_id'] = $user->id;

            return Transaction::create($data);
        });
    }

    public function createFromRecurring(RecurringTransaction $recurring): Transaction
    {
        $data = [
            'wallet_id' => $recurring->wallet_id,
            'category_id' => $recurring->category_id,
            'amount' => $recurring->amount,
            'note' => $recurring->note,
            'date' => now(),
            'is_recurring_transaction' => true,
            'recurring_transaction_id' => $recurring->id,
        ];

        return DB::transaction(function () use ($data) {
            // Lock row wallet to avoid race condition
            $wallet = Wallet::where('id', $data['wallet_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

            if ($wallet->balance < $data['amount']) {
                throw new \RuntimeException(__('transaction.insufficient_balance'));
            }

            $wallet->balance -= $data['amount'];
            $wallet->save();

            return Transaction::create($data);
        });
    }
}
