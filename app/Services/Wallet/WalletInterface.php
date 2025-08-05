<?php

namespace App\Services\Wallet;

use Illuminate\Http\Request;
use App\Models\Wallet;

interface WalletInterface
{
    public function create(array $data);
    public function update(array $data, Wallet $wallet);
}
