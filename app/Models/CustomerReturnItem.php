<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReturnItem extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'customer_return_id',
        'product_id',
        'invoice_item_id',
        'delivery_note_item_id',
        'batch_id',
        'quantity_sold',
        'quantity_returned',
        'unit_price_ht',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total_ttc',
        'reason',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function deliveryNoteItem()
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
