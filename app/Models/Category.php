<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category_parent_id',
        'created_by',
        'default',
        'wallet_type'
    ];

    protected $casts = [
        'default' => 'boolean'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'category_parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'category_parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringTransaction()
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function walletTypes()
    {
        return $this->belongsToMany(WalletType::class, 'category_wallet_types');
    }
}
