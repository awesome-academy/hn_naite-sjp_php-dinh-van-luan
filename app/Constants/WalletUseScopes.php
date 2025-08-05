<?php

namespace App\Constants;

class WalletUseScopes
{
    public const Total = 'total';
    public const Wallet = 'wallet';
    public const ALL = [
           self::Total,
           self::Wallet,
       ];
}
