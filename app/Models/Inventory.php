<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'warehouse_id',
        'inventory_number',
        'total_products',
        'closed_at',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relations
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Lignes de inventory
    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public static function generateInventoryNumber(): string
    {
        return app(DocumentNumberService::class)->generate('inventory');
    }
}
