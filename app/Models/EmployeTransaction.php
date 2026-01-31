<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmployeTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'employe_id',
        'wallet_id',
        'amount',
        'type',
        'date',
        'reference',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
