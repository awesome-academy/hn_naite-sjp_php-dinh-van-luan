<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BasicWalletService implements WalletInterface
{
    public function create(Request $request)
    {
        return Wallet::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'balance'     => $request->balance,
            'currency_id' => $request->currency_id,
            'wallet_type' => 'basic',
        ]);
    }
}
