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
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Rendre les colonnes existantes nullables pour les ajustements d'inventaire
            $table->uuid('invoice_item_id')->nullable()->change();
            $table->uuid('invoice_id')->nullable()->change();

            // Ajouter les nouvelles colonnes requises
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('inventory_id')->nullable()->index();
            $table->uuid('inventory_item_id')->nullable()->index();
            $table->uuid('warehouse_id')->nullable()->index();
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->integer('variance')->default(0);
            $table->uuid('user_id')->nullable()->index();
            $table->string('movement_type')->nullable(); // ex: inventory_adjustment_in, inventory_adjustment_out
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->uuid('invoice_item_id')->nullable(false)->change();
            $table->uuid('invoice_id')->nullable(false)->change();

            $table->dropColumn([
                'tenant_id',
                'inventory_id',
                'inventory_item_id',
                'warehouse_id',
                'quantity_before',
                'quantity_after',
                'variance',
                'user_id',
                'movement_type',
            ]);
        });
    }
};
