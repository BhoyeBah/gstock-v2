<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildReturnProducts();
    }

    public function down(): void
    {
        $this->rebuildReturnProducts();
    }

    private function rebuildReturnProducts(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            if (Schema::hasTable('return_products')) {
                Schema::rename('return_products', 'return_products_old_fk_fix');
            }

            Schema::create('return_products', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id');
                $table->uuid('invoice_item_id');
                $table->uuid('inventory_movement_id')->nullable();
                $table->integer('quantity');
                $table->string('motif');
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->onDelete('cascade');
                $table->foreign('inventory_movement_id')->references('id')->on('inventory_movements')->onDelete('set null');
            });

            if (Schema::hasTable('return_products_old_fk_fix')) {
                DB::statement("
                    INSERT INTO return_products (
                        id, tenant_id, invoice_item_id, inventory_movement_id, quantity, motif, created_at, updated_at
                    )
                    SELECT
                        id, tenant_id, invoice_item_id, inventory_movement_id, quantity, motif, created_at, updated_at
                    FROM return_products_old_fk_fix
                ");

                Schema::drop('return_products_old_fk_fix');
            }

            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        Schema::table('return_products', function (Blueprint $table) {
            $table->dropForeign(['inventory_movement_id']);
            $table->foreign('inventory_movement_id')
                ->references('id')
                ->on('inventory_movements')
                ->nullOnDelete();
        });
    }
};
