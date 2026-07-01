<?php

namespace App\Models;


use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'id',
        'tenant_id',
        'inventory_id',
        'inventory_item_id',
        'invoice_item_id',
        'invoice_id',
        'batch_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'quantity_before',
        'quantity_after',
        'variance',
        'movement_type',
        'source_type',
        'source_id',
        'user_id',
        'movement_date',
        'reason',
        'tenant_id',
        'inventory_id',
        'inventory_item_id',
        'warehouse_id',
        'quantity_before',
        'quantity_after',
        'variance',
        'user_id',
        'movement_type',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Relation vers l’article de facture (InvoiceItem)
     */
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /**
     * Relation vers la facture (Invoice)
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relation vers le lot (Batch)
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Relation vers le produit (Product)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
