<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'quote_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate_id',
        'subtotal_ht',
        'tax_amount',
        'total_ttc',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'discount' => 'integer',
        'subtotal_ht' => 'integer',
        'tax_amount' => 'integer',
        'total_ttc' => 'integer',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
}
