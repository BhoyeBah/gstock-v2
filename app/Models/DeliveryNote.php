<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'sale_order_id',
        'contact_id',
        'warehouse_id',
        'delivery_number',
        'status',
        'delivery_date',
        'validated_at',
        'validated_by',
        'cancelled_at',
        'cancelled_by',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}
