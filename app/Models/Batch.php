<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'id',
        'invoice_id',
        'tenant_id',
        'warehouse_id',
        'product_id',
        'unit_price',
        'quantity',
        'benefit',
        'remaining',
        'expiration_date',
        'source_type',
        'source_id',
        'origin',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =====================
       RELATIONS
       ===================== */

    // Lien avec la facture
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Lien avec l'entrepôt
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Lien avec le produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Lien avec le tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Dans Batch.php
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isExpired()
    {
        if (! empty($this->expiration_date)) {
            return $this->expiration_date < now();
        }

        return false;
    }
}
