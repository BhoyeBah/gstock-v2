<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class walletTransaction extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'source_type',
        'source_id',
        'note',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
