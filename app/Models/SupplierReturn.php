<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'contact_id',
        'supplier_invoice_id',
        'goods_receipt_id',
        'warehouse_id',
        'return_number',
        'status',
        'return_date',
        'reason',
        'validated_at',
        'validated_by',
        'cancelled_at',
        'cancelled_by',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function supplierInvoice()
    {
        return $this->belongsTo(Invoice::class, 'supplier_invoice_id');
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'source_id')->where('source_type', self::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
