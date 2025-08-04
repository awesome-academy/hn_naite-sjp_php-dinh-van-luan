<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Models\SavingWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingWalletService implements WalletInterface
{
    public function create(Request $request)
    {
        $wallet = Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'balance'     => $request->balance,
            'currency_id' => $request->currency_id,
            'wallet_type' => 'saving',
        ]);

        SavingWallet::create([
            'wallet_id'      => $wallet->id,
            'initial_amount' => $wallet->balance,
            'target_amount'  => $request->target_amount,
            'end_date'       => $request->end_date,
        ]);

        return $wallet->load('savingWallet');
    }
}
