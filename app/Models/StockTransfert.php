<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfert extends Model
{
    use HasFactory, HasUuid, HasTenant;



    /**
     * Les attributs pouvant être assignés en masse.
     */
    protected $fillable = [
        'transfer_number',
        'product_id',
        'source_warehouse_id',
        'target_warehouse_id',
        'source_batch_id',
        'target_batch_id',
        'quantity',
    ];

    /**
     * Relations
     */

    // Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Entrepôt source
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    // Entrepôt cible
    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    // Lot source
    public function sourceBatch()
    {
        return $this->belongsTo(Batch::class, 'source_batch_id');
    }

    // Lot cible
    public function targetBatch()
    {
        return $this->belongsTo(Batch::class, 'target_batch_id');
    }
}
