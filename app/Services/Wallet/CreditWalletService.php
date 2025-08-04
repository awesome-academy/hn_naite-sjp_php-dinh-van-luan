<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Models\CreditWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditWalletService implements WalletInterface
{
    public function create(Request $request)
    {
        $wallet = Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'balance'     => $request->balance,
            'currency_id' => $request->currency_id,
            'wallet_type' => 'credit',
        ]);

        CreditWallet::create([
            'wallet_id'        => $wallet->id,
            'credit_limit'     => $request->credit_limit,
            'statement_date'   => $request->statement_date,
            'payment_due_date' => $request->payment_due_date,
        ]);

        return $wallet->load('creditWallet');
    }
}
