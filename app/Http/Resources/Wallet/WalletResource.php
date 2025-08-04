<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Wallet\BasicWalletResource;
use App\Http\Resources\Wallet\SavingWalletResource;
use App\Http\Resources\Wallet\CreditWalletResource;

class WalletResource extends JsonResource
{
    public function toArray($request)
    {
        return static::makeFrom($this)->toArray($request);
    }

    public static function makeFrom($wallet)
    {
        return match ($wallet->wallet_type) {
            'basic'   => new BasicWalletResource($wallet),
            'saving'  => new SavingWalletResource($wallet),
            'credit'  => new CreditWalletResource($wallet),
            default   => new WalletResource($wallet),
        };
    }
}
