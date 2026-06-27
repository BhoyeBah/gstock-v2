<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'invoice_id',
        'quantity',
        'type',
        'unit_price',
        'discount',
        'total_line',
        'expiration_date',
    ];

    /* =====================
     RELATIONS
     ===================== */

    // Lien avec la facture
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Lien avec le produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Lien avec l'entrepôt
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Lien avec le batch
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnProduct::class, 'invoice_item_id');
    }
}
