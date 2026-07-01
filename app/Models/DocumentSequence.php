<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'tenant_id',
        'document_type',
        'prefix',
        'current_number',
        'padding',
        'year',
        'period_key',
        'reset_period',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_number' => 'integer',
        'padding' => 'integer',
        'year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
