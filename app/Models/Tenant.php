<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'ninea',
        'rc',
        'logo',
        'is_active',

    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Un tenant a plusieurs utilisateurs
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Un tenant a plusieurs rôles (Spatie modifié)
    public function roles()
    {
        return $this->hasMany(\Spatie\Permission\Models\Role::class);
    }

    // Un tenant peut avoir plusieurs abonnements
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // L’abonnement actif (si on gère plusieurs historiques)
    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function documentSequences()
    {
        return $this->hasMany(DocumentSequence::class);
    }
}
