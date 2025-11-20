<?php

namespace App\Models;

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

    public static function generateInventoryNumber(): string
    {
        $year = date('Y');

        // Compter le nombre d'inventaires pour l'année en cours
        $count = self::whereYear('created_at', $year)->count() + 1;

        // Formater le numéro : INV-2024-001
        return 'INV-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
