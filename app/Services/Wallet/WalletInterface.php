<?php

namespace App\Services\Wallet;

use Illuminate\Http\Request;

interface WalletInterface
{
    public function create(Request $request);
}
