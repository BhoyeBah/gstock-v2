<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditNoteItem extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'customer_credit_note_id',
        'customer_return_item_id',
        'product_id',
        'quantity',
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

    public function customerCreditNote()
    {
        return $this->belongsTo(CustomerCreditNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customerReturnItem()
    {
        return $this->belongsTo(CustomerReturnItem::class);
    }
}
