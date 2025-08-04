<?php

namespace App\Services\Wallet;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SavingWallet;
use App\Models\Wallet;

class SavingWalletService implements WalletInterface
{
    public function create(array $data)
    {
        $wallet = Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $data['name'],
            'balance'     => $data['balance'],
            'currency_id' => $data['currency_id'],
            'wallet_type' => 'saving',
        ]);

        SavingWallet::create([
            'wallet_id'      => $wallet->id,
            'initial_amount' => $data['initial_amount'],
            'target_amount'  => $data['target_amount'],
            'end_date'       => $data['end_date'],
        ]);

        return $wallet->load('savingWallet');
    }

    public function update(array $data, Wallet $wallet)
    {
        try {
            $wallet->update([
                'name'        => $data['name'],
                'balance'     => $data['balance'],
                'currency_id' => $data['currency_id'],
            ]);

            $savingWallet = SavingWallet::where('wallet_id', $wallet->id)->first();

            if (!$savingWallet) {
                throw new \RuntimeException(__('wallet.linked_saving_wallet_not_found', ['id' => $wallet->id]));
            }

            $savingWallet->update([
               'initial_amount' => $data['initial_amount'],
               'target_amount'  => $data['target_amount'],
               'end_date'       => $data['end_date'],
            ]);

            return $wallet->load('savingWallet');
        } catch (\Exception $e) {
            Log::error("SavingWalletService update failed for wallet_id={$wallet->id}: {$e->getMessage()}", [
                'exception' => $e,
                'data' => $data,
            ]);

            throw new \RuntimeException(__('wallet.update_failed'), previous: $e);
        }
    }
}
