<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('sale_order_id')->index();
            $table->uuid('contact_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->string('delivery_number')->nullable();
            $table->string('status')->default('draft')->index();
            $table->date('delivery_date');
            $table->timestamp('validated_at')->nullable();
            $table->uuid('validated_by')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->uuid('cancelled_by')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('sale_order_id')->references('id')->on('sale_orders')->onDelete('restrict');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['tenant_id', 'delivery_number']);
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delivery_note_id')->index();
            $table->uuid('sale_order_item_id')->nullable()->index();
            $table->uuid('product_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->integer('quantity_ordered');
            $table->integer('quantity_delivered')->default(0);
            $table->integer('quantity_remaining')->default(0);
            $table->timestamps();

            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->onDelete('cascade');
            $table->foreign('sale_order_item_id')->references('id')->on('sale_order_items')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
