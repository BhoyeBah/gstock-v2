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
        // Table Inventories
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('warehouse_id');
            $table->string('inventory_number')->unique();
            $table->integer('total_products')->default(0);
            $table->date("closed_at")->nullable();
            $table->enum('status', ['pending', 'completed'])
                ->default('pending');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // Table Inventory Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->uuid('product_id');
            $table->integer('theoretical_qty')->default(0);
            $table->integer('real_qty')->nullable();
            $table->integer('variance')->nullable();
            $table->boolean('validated')->default(false);
            $table->timestamps();

            // Clés étrangères
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Index pour éviter les doublons et accélérer les requêtes
            $table->unique(['inventory_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventories');
    }
};
