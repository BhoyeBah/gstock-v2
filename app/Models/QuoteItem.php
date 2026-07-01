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
        'quote_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_price_ht',
        'unit_price',
        'discount_amount',
        'discount',
        'subtotal_ht',
        'tax_id',
        'tax_rate_id',
        'tax_rate',
        'tax_amount',
        'total_ttc',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
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
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
