<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleOrder extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'contact_id',
        'quote_id',
        'invoice_id',
        'order_number',
        'order_date',
        'status',
        'total_ht',
        'total_discount',
        'tax_amount',
        'total_ttc',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PARTIALLY_DELIVERED = 'partially_delivered';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_CANCELLED = 'cancelled';

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(SaleOrderItem::class);
    }
}
