<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class walletTransaction extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'payment_id',
        'user_id',
        'type',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'source_type',
        'source_id',
        'description',
        'note',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
