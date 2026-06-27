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
        'tenant_id',
        'contact_id',
        'quote_number',
        'quote_date',
        'expiry_date',
        'status',
        'subtotal_ht',
        'tax_total',
        'total_ttc',
        'converted_invoice_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'expiry_date' => 'date',
        'subtotal_ht' => 'integer',
        'tax_total' => 'integer',
        'total_ttc' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CONVERTED = 'converted';

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function convertedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateQuoteNumber(): string
    {
        if ($this->quote_number) {
            return $this->quote_number;
        }

        if (! $this->tenant_id) {
            throw new \RuntimeException('tenant_id requis pour générer quote_number');
        }

        $this->quote_date = $this->quote_date ?? now();
        $year = \Carbon\Carbon::parse($this->quote_date)->format('Y');
        $base = "DEV-{$year}-";

        $lastQuote = self::where('tenant_id', $this->tenant_id)
            ->whereYear('quote_date', $year)
            ->where('quote_number', 'LIKE', $base.'%')
            ->orderByDesc('quote_number')
            ->first();

        $lastNumber = 0;
        if ($lastQuote && preg_match('/DEV-' . $year . '-(\d+)/', $lastQuote->quote_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $this->quote_number = sprintf('%s%06d', $base, $lastNumber + 1);

        return $this->quote_number;
    }
}
