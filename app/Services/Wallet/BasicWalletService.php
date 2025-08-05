<?php

namespace App\Services\Wallet;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Wallet;

class BasicWalletService implements WalletInterface
{
    public function create(array $data)
    {
        return Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $data['name'],
            'balance'     => $data['balance'],
            'currency_id' => $data['currency_id'],
            'wallet_type' => 'basic',
        ]);
    }

    public function update(array $data, Wallet $wallet)
    {
        try {
            $wallet->update([
            'name'        => $data['name'],
            'balance'     => $data['balance'],
            'currency_id' => $data['currency_id'],
                ]);

            return $wallet;
        } catch (\Exception $e) {
            Log::error("BasicService update failed for wallet_id={$wallet->id}: {$e->getMessage()}", [
              'exception' => $e,
              'data' => $data,
            ]);

            throw new \RuntimeException(__('wallet.update_failed'), previous: $e);
        }
    }
}
