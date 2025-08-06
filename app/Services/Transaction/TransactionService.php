<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
}
