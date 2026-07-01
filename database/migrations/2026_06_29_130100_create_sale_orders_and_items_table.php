<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('contact_id')->index();
            $table->uuid('quote_id')->nullable()->index();
            $table->uuid('created_by')->nullable()->index();
            $table->string('order_number')->nullable();
            $table->date('order_date');
            $table->string('status')->default('draft')->index();
            $table->integer('total_ht')->default(0);
            $table->integer('total_discount')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('quote_id')->references('id')->on('quotes')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tenant_id', 'order_number']);
        });

        Schema::create('sale_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_order_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('warehouse_id')->nullable()->index();
            $table->integer('quantity_ordered');
            $table->integer('quantity_delivered')->default(0);
            $table->integer('quantity_remaining')->default(0);
            $table->integer('unit_price_ht');
            $table->integer('discount_amount')->default(0);
            $table->integer('subtotal_ht')->default(0);
            $table->uuid('tax_id')->nullable()->index();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->timestamps();

            $table->foreign('sale_order_id')->references('id')->on('sale_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
        Schema::dropIfExists('sale_orders');
    }
};
