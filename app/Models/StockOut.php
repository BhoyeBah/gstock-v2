<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory, HasUuid, HasTenant;

    protected $table = 'stock_outs';

    // Colonnes mass assignables
    protected $fillable = [
        'stock_out_number',
        'tenant_id',
        'batch_id',
        'quantity',
        'reason',
    ];

    /**
     * Relation avec le batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Relation avec le tenant (si multi-tenant)
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
