<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use App\Services\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'contact_id',
        'quote_id',
        'sale_order_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'type',
        'total_invoice',
        'total_ht',
        'tax_amount',
        'discount_amount',
        'balance',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Types disponibles
    public const TYPE_CLIENT = 'client';

    public const TYPE_SUPPLIER = 'supplier';

    /* =====================
       SCOPES
       ===================== */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeClients($query)
    {
        return $this->scopeType($query, self::TYPE_CLIENT);
    }

    public function scopeSuppliers($query)
    {
        return $this->scopeType($query, self::TYPE_SUPPLIER);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /* =====================
       RELATIONS
       ===================== */

    // Lien avec le contact (client ou fournisseur)
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    // Lignes de facture
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ✅ Relation avec les paiements de cette facture
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id');
    }

    public function supplierCreditNotes()
    {
        return $this->hasMany(SupplierCreditNote::class, 'supplier_invoice_id', 'id');
    }

    public function customerCreditNotes()
    {
        return $this->hasMany(CustomerCreditNote::class, 'customer_invoice_id', 'id');
    }

    public function completedPayments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'id')
            ->where('status', 'completed');
    }

    /**
     * Génère un invoice_number unique pour cette facture.
     * Utilisable comme $invoice->generateInvoiceNumber();
     */
    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            // Si l'utilisateur a déjà défini un numéro, on ne le regénère pas.
            // Mais tu veux ignorer les numéros manuels pour le calcul, pas pour la génération actuelle.
            return $this->invoice_number;
        }

        if (! $this->tenant_id || ! $this->type) {
            throw new \RuntimeException('tenant_id et type requis pour générer invoice_number');
        }

        $documentType = $this->type === self::TYPE_SUPPLIER
            ? 'supplier_invoice'
            : 'customer_invoice';

        $this->invoice_number = app(DocumentNumberService::class)->generate($documentType, $this->tenant);

        return $this->invoice_number;
    }

}
