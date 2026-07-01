<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditNote extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'credit_note_number',
        'customer_return_id',
        'customer_invoice_id',
        'contact_id',
        'status',
        'credit_date',
        'total_ht',
        'total_discount',
        'tax_amount',
        'total_ttc',
        'applied_amount',
        'remaining_amount',
        'created_by',
        'validated_at',
        'validated_by',
        'cancelled_at',
        'cancelled_by',
        'refunded_at',
        'refunded_by',
        'wallet_id',
        'notes',
    ];

    protected $casts = [
        'credit_date' => 'date',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function customerReturn()
    {
        return $this->belongsTo(CustomerReturn::class, 'customer_return_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'customer_invoice_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(CustomerCreditNoteItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
