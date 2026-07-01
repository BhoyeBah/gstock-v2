<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('return_number')->unique();
            $table->uuid('contact_id')->index();
            $table->uuid('supplier_invoice_id')->nullable()->index();
            $table->uuid('goods_receipt_id')->nullable()->index();
            $table->uuid('warehouse_id')->nullable()->index();
            $table->string('status')->default('draft');
            $table->date('return_date');
            $table->string('reason')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->uuid('validated_by')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->uuid('cancelled_by')->nullable()->index();
            $table->uuid('created_by')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('supplier_invoice_id')->references('id')->on('invoices')->onDelete('restrict');
            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('supplier_return_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('goods_receipt_item_id')->nullable()->index();
            $table->uuid('invoice_item_id')->nullable()->index();
            $table->uuid('batch_id')->nullable()->index();
            $table->integer('quantity_received');
            $table->integer('quantity_returned');
            $table->integer('unit_cost_ht');
            $table->uuid('tax_id')->nullable()->index();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('supplier_return_id')->references('id')->on('supplier_returns')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('goods_receipt_item_id')->references('id')->on('goods_receipt_items')->onDelete('set null');
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->onDelete('set null');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
    }
};
