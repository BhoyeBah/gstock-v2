<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->first()->id ?? null;

        if (!$tenantId) {
            return;
        }

        DB::table('employes')->insert([
            [
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'full_name' => 'John Doe',
                'matricule' => 'EMP001',
                'phone' => '+221770000001',
                'position' => 'Software Developer',
                'salary' => 150000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'full_name' => 'Jane Smith',
                'matricule' => 'EMP002',
                'phone' => '+221770000002',
                'matricule' => 'EMP001',
                'position' => 'HR Manager',
                'salary' => 120000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
