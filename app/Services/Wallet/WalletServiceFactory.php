<?php

namespace App\Services\Wallet;

use InvalidArgumentException;

class WalletServiceFactory
{
    public static function make(string $type): WalletInterface
    {
        return match ($type) {
            'basic'  => new BasicWalletService(),
            'saving' => new SavingWalletService(),
            'credit' => new CreditWalletService(),
            default  => throw new InvalidArgumentException("Wallet type [$type] not supported."),
        };
    }
}
