<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'delivery_note_id',
        'sale_order_item_id',
        'product_id',
        'warehouse_id',
        'quantity_ordered',
        'quantity_delivered',
        'quantity_remaining',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function saleOrderItem()
    {
        return $this->belongsTo(SaleOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
