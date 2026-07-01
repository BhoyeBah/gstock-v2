<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'contact_id',
        'purchase_number',
        'purchase_date',
        'status',
        'total_ht',
        'total_discount',
        'tax_amount',
        'total_ttc',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_CANCELLED = 'cancelled';

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
