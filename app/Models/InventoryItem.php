<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'id',
        'inventory_id',
        'product_id',
        'theoretical_qty',
        'real_qty',
        'variance',
        'validated',
        'status',
        'validated_at',
        'validated_by',
        'reconciled_at',
        'reconciled_by',
        'reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'validated' => 'boolean',
        'validated_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    // Relations
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
