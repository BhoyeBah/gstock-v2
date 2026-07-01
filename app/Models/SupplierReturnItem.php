<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturnItem extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'supplier_return_id',
        'product_id',
        'goods_receipt_item_id',
        'invoice_item_id',
        'batch_id',
        'quantity_received',
        'quantity_returned',
        'unit_cost_ht',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total_ttc',
        'reason',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceiptItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
