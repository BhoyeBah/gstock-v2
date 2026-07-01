<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCreditNote extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'credit_note_number',
        'supplier_return_id',
        'supplier_invoice_id',
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

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class, 'supplier_return_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'supplier_invoice_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierCreditNoteItem::class);
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
