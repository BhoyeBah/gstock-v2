<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, HasTenant, HasUuid;

    /**
     * Les attributs pouvant être remplis en masse.
     */
    protected $fillable = [
        'name',
        'address',
        'description',
        'manager_id',
        'tenant_id',
    ];

    /**
     * Relations : le responsable (utilisateur).
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relation avec le tenant (entreprise).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relation avec les batches par entrepôt.
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

}
