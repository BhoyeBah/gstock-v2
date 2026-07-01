<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'contact_id',
        'quote_number',
        'quote_date',
        'valid_until',
        'expiry_date',
        'status',
        'total_ht',
        'subtotal_ht',
        'total_discount',
        'tax_amount',
        'tax_total',
        'total_ttc',
        'converted_to_sale_order_id',
        'converted_to_invoice_id',
        'converted_sale_order_id',
        'converted_invoice_id',
        'converted_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'converted_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_CANCELLED = 'cancelled';

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'converted_to_sale_order_id');
    }

    public function convertedInvoice()
    {
        return $this->invoice();
    }

    public function convertedSaleOrder()
    {
        return $this->saleOrder();
    }

    public function getConvertedInvoiceIdAttribute(): ?string
    {
        return $this->converted_to_invoice_id;
    }

    public function setConvertedInvoiceIdAttribute($value): void
    {
        $this->attributes['converted_to_invoice_id'] = $value;
    }

    public function getConvertedSaleOrderIdAttribute(): ?string
    {
        return $this->converted_to_sale_order_id;
    }

    public function setConvertedSaleOrderIdAttribute($value): void
    {
        $this->attributes['converted_to_sale_order_id'] = $value;
    }

    public function getExpiryDateAttribute()
    {
        return $this->valid_until;
    }

    public function setExpiryDateAttribute($value): void
    {
        $this->attributes['valid_until'] = $value;
        $this->attributes['expiry_date'] = $value;
    }

    public function getSubtotalHtAttribute()
    {
        return $this->attributes['total_ht'] ?? $this->attributes['subtotal_ht'] ?? null;
    }

    public function setSubtotalHtAttribute($value): void
    {
        $this->attributes['total_ht'] = $value;
        $this->attributes['subtotal_ht'] = $value;
    }

    public function getTaxTotalAttribute()
    {
        return $this->attributes['tax_amount'] ?? $this->attributes['tax_total'] ?? null;
    }

    public function setTaxTotalAttribute($value): void
    {
        $this->attributes['tax_amount'] = $value;
        $this->attributes['tax_total'] = $value;
    }
}
