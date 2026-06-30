<?php

namespace Database\Seeders;

use App\Models\Units;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'code' => 'pcs'],
            ['name' => 'Kilogram', 'code' => 'kg'],
            ['name' => 'Litre', 'code' => 'L'],
            ['name' => 'Box', 'code' => 'box'],
        ];

         foreach ($units as $unit) {
            Units::updateOrCreate(
                ['code' => $unit['code']],
                ['name' => $unit['name']]
            );
        }
    }
}
