<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'sale_order_id',
        'product_id',
        'warehouse_id',
        'quantity_ordered',
        'quantity_delivered',
        'quantity_remaining',
        'unit_price_ht',
        'discount_amount',
        'subtotal_ht',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total_ttc',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
