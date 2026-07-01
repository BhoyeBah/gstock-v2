<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCreditNoteItem extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'supplier_credit_note_id',
        'supplier_return_item_id',
        'product_id',
        'quantity',
        'unit_cost_ht',
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

    public function creditNote()
    {
        return $this->belongsTo(SupplierCreditNote::class, 'supplier_credit_note_id');
    }

    public function returnItem()
    {
        return $this->belongsTo(SupplierReturnItem::class, 'supplier_return_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
