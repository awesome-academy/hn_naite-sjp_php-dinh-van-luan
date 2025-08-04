<?php

namespace App\Services\Wallet;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\CreditWallet;
use App\Models\Wallet;

class CreditWalletService implements WalletInterface
{
    public function create(array $data)
    {
        $wallet = Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $data['name'],
            'balance'     => $data['balance'],
            'currency_id' => $data['currency_id'],
            'wallet_type' => 'credit',
        ]);

        CreditWallet::create([
            'wallet_id'        => $wallet->id,
            'credit_limit'     => $data['credit_limit'],
            'statement_date'   => $data['statement_date'],
            'payment_due_date' => $data['payment_due_date'],
        ]);

        return $wallet->load('creditWallet');
    }

    public function update(array $data, Wallet $wallet)
    {
        try {
            $wallet->update([
                'name'        => $data['name'],
                'balance'     => $data['balance'],
                'currency_id' => $data['currency_id'],
            ]);

            $credit = CreditWallet::where('wallet_id', $wallet->id)->first();

            if (!$credit) {
                throw new \RuntimeException(__('wallet.linked_credit_wallet_not_found', ['id' => $wallet->id]));
            }

            $credit->update([
                'credit_limit'     => $data['credit_limit'],
                'statement_date'   => $data['statement_date'],
                'payment_due_date' => $data['payment_due_date'],
            ]);

            return $wallet->load('creditWallet');
        } catch (\Exception $e) {
            Log::error("CreditWalletService update failed for wallet_id={$wallet->id}: {$e->getMessage()}", [
                'exception' => $e,
                'data' => $data,
            ]);

            throw new \RuntimeException(__('wallet.update_failed'), previous: $e);
        }
    }
}
