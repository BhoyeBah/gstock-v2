<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Supprimer l’unique global
            $table->dropUnique('inventories_inventory_number_unique');

            // Ajouter l’unique par tenant
            $table->unique(['tenant_id', 'inventory_number'], 'inventories_tenant_inventory_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('inventories_tenant_inventory_unique');
            $table->unique('inventory_number');
        });
    }
};
