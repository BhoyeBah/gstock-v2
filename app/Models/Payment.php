<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasTenant, HasUuid;

    /**
     * Les attributs qui peuvent être remplis en masse.
     */
    protected $fillable = [
        'payment_number',
        'wallet_id',
        'invoice_id',
        'tenant_id',
        'contact_id',
        'amount_paid',
        'remaining_amount',
        'payment_date',
        'payment_type',
        'payment_source',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];


    /**
     * Relation : un paiement appartient à une facture.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relation : un paiement appartient à un tenant (locataire).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relation : un paiement appartient à un tenant (locataire).
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(walletTransaction::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
