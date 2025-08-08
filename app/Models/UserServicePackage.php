<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserServicePackage extends Model
{
    use HasFactory;

    protected $fillable = [
       'service_package_id',
       'user_id',
       'register_date',
       'expire_date',
       'payment_method',
       'amount',
       'status',
       'transaction_id',
       'order_id',
    ];

    public const STATUS_PAID = 'paid';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_CANCELED = 'canceled';

    public const METHOD_MOMO = 'momo';
    public const METHOD_VNPAY = 'vnpay';

    protected $casts = [
        'register_date' => 'datetime',
        'expire_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class);
    }
}
