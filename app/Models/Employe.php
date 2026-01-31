<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory, HasTenant, HasUuid;

    protected $fillable = [
        'id',
        'matricule',
        'full_name',
        'phone',
        'position',
        'salary',
    ];

    /**
     * Génération automatique du matricule
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employe) {

            // Si déjà défini, on ne touche pas
            if (! empty($employe->matricule)) {
                return;
            }

            $year = Carbon::now()->year;

            // Dernier employé du tenant pour l'année en cours
            $lastEmploye = self::where('tenant_id', $employe->tenant_id)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc')
                ->first();

            $number = 1;

            if ($lastEmploye && $lastEmploye->matricule) {

                $lastNumber = (int) substr($lastEmploye->matricule, -3);
                $number = $lastNumber + 1;
            }

            $employe->matricule = 'EMP-'.$year.'-'.str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }

    public function transactions()
    {
        return $this->hasMany(EmployeTransaction::class, 'employe_id')->orderByDesc('date');
    }
}
