<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'wallet_id',
        'status',
        'opening_amount',
        'expected_amount',
        'counted_amount',
        'difference',
        'opened_at',
        'closed_at',
        'note',
    ];

    protected $casts = [
        'opening_amount' => 'integer',
        'expected_amount' => 'integer',
        'counted_amount' => 'integer',
        'difference' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    /* =====================
       SCOPES
       ===================== */

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /* =====================
       RELATIONS
       ===================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'cash_session_id');
    }

    /**
     * Total des encaissements réels rattachés à cette session.
     */
    public function collectedAmount(): int
    {
        return (int) $this->payments()->sum('amount_paid');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
