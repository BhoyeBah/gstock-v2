<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('quote_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('tax_rate_id')->nullable()->index();
            $table->integer('quantity');
            $table->integer('unit_price');
            $table->integer('discount')->default(0);
            $table->integer('subtotal_ht')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
