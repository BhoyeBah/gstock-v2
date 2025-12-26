<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'name',
        'code',
        'identifier',
        'initial_balance',
        'current_balance',
        'type',
        'is_active',
    ];

    public function transactions()
    {
        return $this->hasMany(walletTransaction::class);
    }

}
