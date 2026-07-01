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
        'status',
        'total_ht',
        'total_discount',
        'tax_amount',
        'total_ttc',
        'converted_to_sale_order_id',
        'converted_to_invoice_id',
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
}
