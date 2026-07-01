<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()
            ->where('slug', '!=', 'platform')
            ->orderByDesc('created_at')
            ->value('id')
            ?? DB::table('tenants')->first()->id
            ?? null;

        if (!$tenantId) {
            return;
        }

        DB::table('employes')->upsert(array_map(function (array $employe) use ($tenantId) {
            return [
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'full_name' => $employe['full_name'],
                'matricule' => $employe['matricule'],
                'phone' => $employe['phone'],
                'position' => $employe['position'],
                'salary' => $employe['salary'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, [
            [
                'full_name' => 'John Doe',
                'matricule' => 'EMP001',
                'phone' => '+221770000001',
                'position' => 'Software Developer',
                'salary' => 150000,
            ],
            [
                'full_name' => 'Jane Smith',
                'matricule' => 'EMP002',
                'phone' => '+221770000002',
                'position' => 'HR Manager',
                'salary' => 120000,
            ],
        ]), ['tenant_id', 'matricule'], ['full_name', 'phone', 'position', 'salary', 'updated_at']);
    }
}
