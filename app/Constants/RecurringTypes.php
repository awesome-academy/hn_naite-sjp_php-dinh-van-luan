<?php

namespace App\Constants;

class RecurringTypes
{
    public const Weekly = 'weekly';
    public const Monthly = 'monthly';
    public const Quarterly = 'quarterly';
    public const Yearly = 'yearly';
    public const Option = 'option';
    public const ALL = [
           self::Weekly,
           self::Monthly,
           self::Quarterly,
           self::Yearly,
           self::Option,
       ];
}
