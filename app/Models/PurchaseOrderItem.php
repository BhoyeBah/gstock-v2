<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'warehouse_id',
        'quantity_ordered',
        'quantity_received',
        'quantity_remaining',
        'unit_cost_ht',
        'discount_amount',
        'subtotal_ht',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total_ttc',
        'expiration_date',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'expiration_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
